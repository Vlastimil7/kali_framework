<?php

namespace Services\AI;

use Helpers\Logger;

final class ChatService
{
    private AiClientInterface $client;
    private static ?array $personalDataCache = null;
    private array $personalData = [];


    public function __construct(?string $provider = null)
    {
        $provider ??= defined('AI_PROVIDER') ? (string)AI_PROVIDER : 'openai';
        $this->client = AiClientFactory::make($provider);
        $this->loadPersonalData();

        Logger::debug('ChatService initialized', [
            'provider' => $this->client->name(),
            'personalDataLoaded' => !empty($this->personalData),
        ]);
    }

    public function getProvider(): string
    {
        return $this->client->name();
    }

    public function testConnection(): bool
    {
        return $this->client->testConnection();
    }

    public function getStatus(): array
    {
        return array_merge([
            'personal_data_loaded' => !empty($this->personalData),
        ], $this->client->getStatus());
    }

    private function nowContext(): array
    {
        // nastav timezone – ideálně z configu, nebo natvrdo Europe/Prague
        $tz = defined('APP_TIMEZONE') ? (string)APP_TIMEZONE : 'Europe/Prague';
        $dt = new \DateTimeImmutable('now', new \DateTimeZone($tz));


        return [
            'timezone' => $tz,
            'iso' => $dt->format('c'),
            'date' => $dt->format('Y-m-d'),
            'time' => $dt->format('H:i:s'),
            'weekday' => $dt->format('l'), // Monday...
            // český den v týdnu – jednoduchá mapa
            'weekday_cs' => [
                'Monday' => 'pondělí',
                'Tuesday' => 'úterý',
                'Wednesday' => 'středa',
                'Thursday' => 'čtvrtek',
                'Friday' => 'pátek',
                'Saturday' => 'sobota',
                'Sunday' => 'neděle',
            ][$dt->format('l')] ?? $dt->format('l'),
        ];
    }

    private function isInScope(string $question): bool
    {
        $q = mb_strtolower($question);

        $allow = [

            // ===== WEB / DEVELOPMENT =====
            'web',
            'weby',
            'webová stránka',
            'webové stránky',
            'stránky',
            'site',
            'website',
            'websites',
            'e-shop',
            'eshop',
            'shop',
            'store',
            'online store',
            'webshop',
            'ecommerce',
            'e-commerce',
            'aplikace',
            'aplikaci',
            'application',
            'app',
            'software',
            'system',
            'platform',
            'api',
            'integrace',
            'integration',
            'napojení',
            'propojení',
            'automation',
            'automatizace',
            'databáze',
            'database',
            'backend',
            'frontend',
            'cms',

            // ===== TECHNOLOGIE =====
            'react',
            'php',
            'javascript',
            'tailwind',
            'node',
            'next',
            'framework',
            'seo',
            'analytics',
            'analytika',
            'tracking',
            'performance',
            'rychlost',
            'optimalizace',
            'security',
            'bezpečnost',
            'zabezpečení',

            // ===== SLUŽBY =====
            'služby',
            'sluzby',
            'services',
            'nabízíš',
            'nabízíte',
            'nabidka',
            'nabídka',
            'offer',
            'provide',
            'do you build',
            'co umíš',
            'co děláš',
            'what do you do',
            'what do you offer',

            // ===== CENA =====
            'cena',
            'ceník',
            'kolik',
            'kolik stojí',
            'rozpočet',
            'budget',
            'price',
            'pricing',
            'cost',
            'estimate',
            'quote',
            'rate',
            'hourly',

            // ===== PROCES =====
            'jak dlouho',
            'termín',
            'deadline',
            'timeline',
            'delivery',
            'proces',
            'postup',
            'workflow',
            'steps',
            'spolupráce',
            'cooperation',

            // ===== KONTAKT =====
            'kontakt',
            'kontaktovat',
            'kontaktujte',
            'spojit',
            'spojit se',
            'ozvat',
            'ozvěte se',
            'email',
            'mail',
            'telefon',
            'phone',
            'call',
            'schůzka',
            'meeting',
            'formulář',
            'formular',
            'contact form',
            'get in touch',
            'reach out',

            // ===== NABÍDKA / POPTÁVKA =====
            'poptávka',
            'poptavka',
            'inquiry',
            'enquiry',
            'nabídku',
            'nabidku',
            'proposal',
            'konzultace',
            'consultation',
            'projekt',
            'project',
            'realizace',
            'implementation',
            'vývoj',
            'development',
            'redesign',
            'create',
            'build'

        ];


        $deny = [
            // CZ...
            'politika',
            'volby',
            'prezident',
            'zdraví',
            'léky',
            'diagnóza',
            'investice',
            'krypt',
            'akcie',
            'crack',
            'ddos',
            'phishing',
            'osobní život',
            'manželka',
            'děti',

            // EN
            'politics',
            'election',
            'president',
            'medicine',
            'drug',
            'diagnosis',
            'investment',
            'crypto',
            'stock',
            'shares',
            'cracking',
            'ddos',
            'phishing',
            'personal life',
            'wife',
            'kids',
            'children'
        ];

        $allow = array_values(array_unique($allow));
        $deny  = array_values(array_unique($deny));

        foreach ($deny as $w) {
            if (mb_strpos($q, $w) !== false) return false;
        }

        foreach ($allow as $w) {
            if (mb_strpos($q, $w) !== false) return true;
        }

        if (count(preg_split('/\s+/u', trim($q))) > 4) return true;

        return false;
    }


    public function ask(string $question): string
    {
        $lang = $this->getCurrentLang();

        if (trim($question) === '') {
            return $this->greetingText($lang);
        }

        if (empty($this->personalData)) {
            return $this->noDataText($lang);
        }

        if ($this->isGreeting($question)) {
            return $this->greetingText($lang);
        }

        if (!$this->isContinuation($question) && !$this->isInScope($question)) {
            return $this->outOfScopeText($lang);
        }

        if ($this->isCompanyAgeQuestion($question)) {
            $founded = $this->personalData['brand']['founded_date'] ?? null;

            if (!$founded) {
                return $lang === 'en'
                    ? "I don't have that information available. Please use the contact form and I’ll confirm it for you."
                    : "Tuhle informaci nemám v podkladech. Napište prosím přes kontaktní formulář a ověřím to.";
            }

            // spočítej roky (přesněji klidně i měsíce)
            $tz = defined('APP_TIMEZONE') ? (string)APP_TIMEZONE : 'Europe/Prague';
            $now = new \DateTimeImmutable('now', new \DateTimeZone($tz));
            $dt  = new \DateTimeImmutable($founded, new \DateTimeZone($tz));
            $diff = $dt->diff($now);
            $years = (int)$diff->y;

            return $lang === 'en'
                ? "VK-DEV was founded on {$dt->format('Y-m-d')}. That’s about {$years} year(s) on the market."
                : "VK-DEV vzniklo {$dt->format('d. m. Y')}. Na trhu jsme zhruba {$years} rok/roky.";
        }


        $t0 = microtime(true);

        Logger::info('ChatService ask start', [
            'provider' => $this->client->name(),
            'q_len'    => mb_strlen($question),
            'q_head'   => $this->truncateForLog($question, 300),
        ]);

        $relevantData = $this->findRelevantData($question);
        $relevantData['brand']   = $this->personalData['brand'] ?? [];
        $relevantData['contact'] = $this->personalData['contact'] ?? [];
        $relevantData['now'] = $this->nowContext();
        $relevantData['lang'] = $lang;
        $relevantData = $this->localizeData($relevantData, $lang);


        $system = $this->buildSystemPrompt($relevantData);

        Logger::debug('ChatService prompt ready', [
            'provider'        => $this->client->name(),
            'system_len'      => mb_strlen($system),
            'relevant_keys'   => array_keys($relevantData),
        ]);

        // ✅ JEDINÉ místo kde se volá AI
        $answer = $this->callWithRetry($system, $question);

        $ms = (int) round((microtime(true) - $t0) * 1000);

        Logger::info('ChatService ask done', [
            'provider'    => $this->client->name(),
            'total_ms'    => $ms,
            'answer_len'  => mb_strlen($answer),
            'answer_head' => $this->truncateForLog($answer, 350),
        ]);

        return $answer;
    }


    private function callWithRetry(string $system, string $question): string
    {
        $maxAttempts = 3;
        $baseDelayMs = 300; // 0.3s, pak 0.6s, 1.2s (+ jitter)

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $t0 = microtime(true);
            $answer = $this->client->ask($system, $question);
            $ms = (int) round((microtime(true) - $t0) * 1000);

            // success (heuristika: nezačíná varováním)
            if (strpos($answer, '⚠️') !== 0) {
                Logger::info('AI call success', [
                    'provider' => $this->client->name(),
                    'attempt'  => $attempt,
                    'ai_ms'    => $ms,
                ]);
                return $answer;
            }

            // retryable?
            $retryable =
                (strpos($answer, '(529)') !== false) ||
                (strpos($answer, '(429)') !== false) ||
                (strpos($answer, '(503)') !== false) ||
                (strpos($answer, '(502)') !== false) ||
                (strpos($answer, '(504)') !== false) ||
                (stripos($answer, 'Overloaded') !== false);

            Logger::warning('AI call failed', [
                'provider' => $this->client->name(),
                'attempt'  => $attempt,
                'ai_ms'    => $ms,
                'retryable' => $retryable,
                'error_head' => $this->truncateForLog($answer, 200),
            ]);

            if (!$retryable || $attempt === $maxAttempts) {
                return $answer; // konec, vrať error text
            }

            // backoff + jitter
            $delay = (int) ($baseDelayMs * (2 ** ($attempt - 1)));
            $jitter = random_int(0, 120);
            usleep(($delay + $jitter) * 1000);
        }

        return "⚠️ **Chyba:** Retry mechanism failed unexpectedly.";
    }


    private function truncateForLog(string $text, int $maxLen): string
    {
        $text = trim($text);
        return (mb_strlen($text) <= $maxLen) ? $text : (mb_substr($text, 0, $maxLen) . '…');
    }



    private function buildSystemPrompt(array $relevantData): string
    {
        $lang = $relevantData['lang'] ?? $this->getCurrentLang();
        $dataForPrompt = $relevantData;
        unset($dataForPrompt['lang'], $dataForPrompt['now']);

        if ($lang === 'en') {

            $system  = "You are a professional AI assistant representing VK-DEV, a company specializing in custom websites, e-commerce solutions, web applications, APIs, integrations, maintenance, and security.\n";

            $system .= "IMPORTANT RULES:\n";
            $system .= "- Respond ONLY in English.\n";
            $system .= "- Never mix languages.\n";
            $system .= "- Only answer questions related to VK-DEV services.\n";
            $system .= "- If the request is outside this scope, politely refuse and guide the user back to business topics.\n";
            $system .= "- Never invent information. If data is missing, say: \"I don't have that information available.\" \n";
            $system .= "- Do not provide medical, legal, financial, or political advice.\n\n";
            $system .= "- Do not provide instructions for hacking, phishing, exploiting vulnerabilities, or any wrongdoing. You may only provide defensive security best practices.\n";
            $system .= "- Use only the provided data as your source of truth.\n\n";
            $system .= "- If the user asks how to contact you / request a quote, ALWAYS provide the contact options from the provided data (email/phone/contact form link) and propose the next step.\n";
            $system .= "- Never claim we provide a technology unless it is explicitly listed in the provided data.\n";
            $system .= "- Never guess.";
            $system .= "- If the user asks for a technology or service that is NOT listed in the provided data, treat it as NOT OFFERED and say it clearly (e.g., \"We do not offer .NET development at the moment\").\n";
            $system .= "- Never say \"I don't have that information\" for technologies. Instead, state that it is not offered.\n";
            $system .= "- If the request is outside our stack, propose an alternative within our stack (e.g., web app with PHP/React + REST API).\n";
            $system .= "- Responses must be decisive: yes/no + next step.\n";
            $system .= "- Follow stack.primary and stack.not_offered. If a technology is in stack.not_offered, always state it is not offered.\n";
            $system .= "- Company history (\"how long in business\", \"since when\", \"years on the market\", \"number of projects\") is allowed ONLY if explicitly present in the provided data (brand.founded_date / brand.years_on_market / brand.projects_count).\n";
            $system .= "- If missing, reply exactly: \"I don't have that information available.\" and offer contact options.\n";
            $system .= "- Never invent a founding year, years on the market, or number of projects.\n";
            $system .= "- If brand.founded_date exists, you may compute years using CURRENT TIME.\n";






            $system .= "CURRENT TIME (SOURCE OF TRUTH):\n";
            $system .= json_encode($relevantData['now'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $system .= "\n\n";
            $system .= "BUSINESS DATA (ONLY SOURCE):\n";
            $system .= json_encode($dataForPrompt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $system .= "\n\n";
            $system .= "Be friendly and concise. If the request is vague, ask up to 3 follow-up questions.\n";
        } else {

            $system  = "Jsi profesionální AI asistent reprezentující VK-DEV, firmu zaměřenou na vývoj webů, e-shopů, webových aplikací, API, integrací, údržby a bezpečnosti.\n";

            $system .= "DŮLEŽITÁ PRAVIDLA:\n";
            $system .= "- Odpovídej pouze česky.\n";
            $system .= "- Nikdy nemíchej jazyky.\n";
            $system .= "- Odpovídej pouze na dotazy týkající se služeb VK-DEV.\n";
            $system .= "- Pokud je dotaz mimo tento rozsah, slušně odmítni a nasměruj uživatele zpět k tématu služeb.\n";
            $system .= "- Nikdy si nevymýšlej informace. Pokud data nemáš, řekni: \"Tohle nemám v podkladech.\" \n";
            $system .= "- Neposkytuj zdravotní, právní ani finanční poradenství.\n\n";
            $system .= "- Nedávej návody na hacking, phishing, zneužití zranitelností ani jiné škodlivé věci. Můžeš dávat pouze obranná doporučení (best practices).\n";
            $system .= "- Používej pouze poskytnutá data jako svůj jediný zdroj pravdy.\n\n";
            $system .= "- Pokud se uživatel ptá jak vás kontaktovat / získat nabídku, VŽDY uveď kontaktní možnosti z podkladů (email/telefon/odkaz na kontaktní formulář) a navrhni další krok.\n";
            $system .= "- Nikdy netvrď, že poskytujeme technologii, která není explicitně uvedena v podkladech.\n";
            $system .= "- Nikdy nehadej.";
            $system .= "- Pokud se uživatel ptá na technologii nebo službu, která NENÍ uvedena v podkladech, ber ji jako NEPOSKYTUJEME a řekni to jasně (např. \"Tuto technologii momentálně nenabízíme\").\n";
            $system .= "- Nikdy neříkej \"nemám v podkladech\" u technologií. Místo toho řekni přímo, že to nenabízíme.\n";
            $system .= "- Pokud je dotaz na technologii mimo náš stack, nabídni alternativu v našem stacku (např. webová aplikace v PHP/React + REST API).\n";
            $system .= "- Odpověď musí být rozhodná: ano/ne + další krok.\n";
            $system .= "- Řiď se sekcí stack.primary a stack.not_offered. Pokud je technologie v stack.not_offered, vždy řekni, že ji nenabízíme.\n";
            $system .= "- Historie firmy (\"jak dlouho na trhu\", \"od kdy\", \"kolik let\", \"kolik projektů\") je POVOLENA jen pokud je explicitně v podkladech (brand.founded_date / brand.formed_date / brand.ico_date / brand.years_on_market / brand.projects_count).\n";
            $system .= "- Pokud tato data v podkladech nejsou, odpověz přesně: \"Tuhle informaci nemám v podkladech.\" a nabídni kontakt.\n";
            $system .= "- Nikdy nevymýšlej rok založení, počet let na trhu ani počet projektů.\n";
            $system .= "- Pokud existuje brand.founded_date, dopočítej roky pouze z něj a z CURRENT TIME.\n";






            $system .= "AKTUÁLNÍ ČAS (ZDROJ PRAVDY):\n";
            $system .= json_encode($relevantData['now'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $system .= "\n\n";
            $system .= "PODKLADY (JEDINÝ ZDROJ):\n";
            $system .= json_encode($dataForPrompt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $system .= "\n\n";
            $system .= "Odpověz přátelsky a věcně. Když je dotaz moc obecný, polož max 3 doplňující otázky.\n";
        }
        return $system;
    }


    private function loadPersonalData(): void
    {
        if (self::$personalDataCache !== null) {
            $this->personalData = self::$personalDataCache;
            return;
        }

        $jsonPath = __DIR__ . '/../../data/personal_data.json';

        if (!file_exists($jsonPath)) {
            $this->personalData = [];
            Logger::warning('Personal data file not found', ['path' => $jsonPath]);
            self::$personalDataCache = $this->personalData;
            return;
        }

        $raw  = file_get_contents($jsonPath);
        $data = json_decode($raw ?: '[]', true);

        $this->personalData = is_array($data) ? $data : [];
        self::$personalDataCache = $this->personalData;

        Logger::debug('Personal data loaded', [
            'path' => $jsonPath,
            'ok'   => !empty($this->personalData),
        ]);
    }

    private function isContinuation(string $q): bool
    {
        $q = trim(mb_strtolower($q));

        // CZ + EN potvrzení / pokračování konverzace
        $continuations = [
            'ano',
            'jo',
            'jasně',
            'ok',
            'okej',
            'dobře',
            'určitě',
            'klidně',
            'pojď',
            'pokračuj',
            'zeptej',
            'ptej se',
            'ptej',
            'zeptejte se',
            'zeptej se',
            'můžeš',
            'muzes',
            'yes',
            'yep',
            'ok',
            'okay',
            'sure',
            'go ahead',
            'continue'
        ];

        // buď přesná shoda, nebo velmi krátká věta typu "ano zeptej"
        if (in_array($q, $continuations, true)) return true;
        $words = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) <= 3) {
            foreach ($continuations as $w) {
                if ($q === $w) return true;
                if (str_starts_with($q, $w . ' ')) return true;
            }
        }
        return false;
    }


    private function localizeData($data, string $lang): mixed
    {
        if (is_array($data)) {
            // pokud to vypadá jako {cs:..., en:...}
            $hasCs = array_key_exists('cs', $data);
            $hasEn = array_key_exists('en', $data);

            if ($hasCs || $hasEn) {
                if (array_key_exists($lang, $data)) {
                    return $this->localizeData($data[$lang], $lang);
                }
                // fallback když chybí požadovaný jazyk
                if (array_key_exists('cs', $data)) return $this->localizeData($data['cs'], $lang);
                if (array_key_exists('en', $data)) return $this->localizeData($data['en'], $lang);
            }

            $out = [];
            foreach ($data as $k => $v) {
                $out[$k] = $this->localizeData($v, $lang);
            }
            return $out;
        }

        return $data;
    }

    private function isCompanyAgeQuestion(string $q): bool
    {
        $q = mb_strtolower($q);
        $needles = [
            'jak dlouho',
            'jak jste dlouho',
            'na trhu',
            'od kdy',
            'kdy vznikl',
            'how long',
            'years',
            'since when',
            'when was',
            'founded'
        ];
        foreach ($needles as $n) {
            if (mb_strpos($q, $n) !== false) return true;
        }
        return false;
    }



    private function greetingText(string $lang): string
    {
        return $lang === 'en'
            ? "Hi! 👋 How can I help you with a website, e-shop, or web application? Please briefly describe what you need, your timeline, and ideally your budget."
            : "Ahoj! 👋 S čím ti můžu pomoct ohledně webu, e-shopu nebo webové aplikace? Napiš prosím stručně co potřebuješ, termín a ideálně rozpočet.";
    }


    private function getCurrentLang(): string
    {
        if (function_exists('lang')) {
            $l = lang()->getCurrentLanguage();
            if (in_array($l, ['cs', 'en'], true)) {
                return $l;
            }
        }

        return 'cs';
    }


    private function isGreeting(string $q): bool
    {
        $q = trim(mb_strtolower($q));
        return in_array($q, [
            'ahoj',
            'dobrý den',
            'dobry den',
            'čau',
            'cau',
            'hello',
            'hi',
            'hey',
            'good morning',
            'good afternoon'
        ], true);
    }



    private function findRelevantData(string $question): array
    {
        $q = mb_strtolower($question);
        $relevant = [];

        $map = [

            'offer' => [
                // CZ
                'služb',
                'nabíz',
                'děláš',
                'umíš',
                'web',
                'stránk',
                'e-shop',
                'eshop',
                'aplikac',
                'api',
                'integrac',
                'databáz',
                'bezpečnost',
                'údržb',
                'podpor',

                // EN
                'service',
                'offer',
                'provide',
                'do you build',
                'website',
                'web',
                'webshop',
                'ecommerce',
                'e-commerce',
                'application',
                'api',
                'integration',
                'database',
                'security',
                'maintenance',
                'support'
            ],

            'pricing' => [
                // CZ
                'cen',
                'kolik',
                'rozpočet',
                'sazb',
                'hodin',
                'balíč',
                'stojí',
                'cena',

                // EN
                'price',
                'cost',
                'budget',
                'rate',
                'hourly',
                'package',
                'pricing',
                'how much',
                'quote',
                'estimate'
            ],

            'process' => [
                // CZ
                'jak probí',
                'postup',
                'krok',
                'spoluprác',
                'proces',
                'harmonogram',
                'doba',
                'termín',

                // EN
                'process',
                'workflow',
                'steps',
                'timeline',
                'delivery time',
                'how long',
                'cooperation',
                'project flow'
            ],

            'contact' => [
                // CZ
                'kontakt',
                'email',
                'napsat',
                'spojit',
                'poptávk',
                'nezávazn',
                'schůzk',
                'zavolat',

                // EN
                'contact',
                'email',
                'reach',
                'get in touch',
                'call',
                'meeting',
                'inquiry',
                'enquiry',
                'request'
            ]
        ];


        foreach ($map as $key => $words) {
            foreach ($words as $w) {
                if (mb_strpos($q, $w) !== false && isset($this->personalData[$key])) {
                    $relevant[$key] = $this->personalData[$key];
                    break;
                }
            }
        }

        // fallback: když nepoznám, pošli minimum (brand + offer)
        if (empty($relevant)) {
            $relevant['brand'] = $this->personalData['brand'] ?? [];
            $relevant['offer'] = $this->personalData['offer'] ?? [];
            $relevant['pricing'] = $this->personalData['pricing'] ?? [];
            $relevant['contact'] = $this->personalData['contact'] ?? [];
        }

        return $relevant;
    }

    private function outOfScopeText(string $lang): string
    {
        return $lang === 'en'
            ? "I can only assist with VK-DEV services such as websites, e-shops, web applications, APIs, integrations, maintenance, and security. Please describe your project and I will propose a suitable solution."
            : "Pomáhám pouze se službami VK-DEV jako jsou weby, e-shopy, webové aplikace, API, integrace, údržba a bezpečnost. Popište prosím svůj projekt a navrhnu vám vhodné řešení.";
    }

    private function noDataText(string $lang): string
    {
        return $lang === 'en'
            ? "Service data is currently unavailable. Please try again later or contact me through the contact form on www.vk-dev.cz/contact."
            : "Momentálně nemám načtené podklady o službách. Zkuste to prosím později nebo mě kontaktujte přes formulář webový formulář na adrese www.vk-dev.cz/contact.";
    }
}
