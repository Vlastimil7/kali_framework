<?php

namespace Helpers;

final class BreadcrumbHelper
{
    /** @var list<array{title: string, url: ?string}> */
    private array $items = [];

    public static function auto(?string $path = null): self
    {
        $breadcrumbs = self::manual();
        $segments = explode('/', trim($path ?? current_route_path(), '/'));
        $accumulated = [];
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            $accumulated[] = $segment;
            $breadcrumbs->add($segment, implode('/', $accumulated));
        }
        return $breadcrumbs;
    }

    public static function manual(): self
    {
        $breadcrumbs = new self();
        $breadcrumbs->items[] = [
            'title' => __('home', [], 'breadcrumbs'),
            'url' => locale_url(),
        ];
        return $breadcrumbs;
    }

    public function add(string $titleKey, ?string $url = null): self
    {
        $this->items[] = [
            'title' => __($titleKey, [], 'breadcrumbs'),
            'url' => $url === null ? null : locale_url($url),
        ];
        return $this;
    }

    /** @return list<array{title: string, url: ?string}> */
    public function all(): array
    {
        return $this->items;
    }
}
