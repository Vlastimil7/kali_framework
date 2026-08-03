<?php

namespace Services\Mail;

use InvalidArgumentException;
use RuntimeException;

final class TemplateRenderer
{
    public function __construct(
        private readonly string $templateRoot = ROOT_PATH . '/src/views/emails',
    ) {
    }

    public function render(string $template, array $data, string $subject = ''): string
    {
        $templateFile = $this->resolve($template);
        $content = $this->includeTemplate($templateFile, $data);
        $layoutFile = $this->templateRoot . '/layout.php';

        if (!is_file($layoutFile)) {
            return $content;
        }

        return $this->includeTemplate($layoutFile, array_merge($data, [
            'content' => $content,
            'subject' => $subject,
        ]));
    }

    private function resolve(string $template): string
    {
        if (!preg_match('~^[a-zA-Z0-9_/-]+$~', $template) || str_contains($template, '..')) {
            throw new InvalidArgumentException('Invalid email template name.');
        }

        $file = $this->templateRoot . '/' . trim($template, '/') . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Email template not found: {$template}");
        }
        return $file;
    }

    private function includeTemplate(string $file, array $data): string
    {
        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $file;
            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }
}
