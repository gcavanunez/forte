<?php

declare(strict_types=1);

use Forte\Rewriting\Passes\Elements\SetAttribute;

describe('Element SetAttribute', function (): void {
    it('sets attribute on matching elements', function (): void {
        $doc = $this->parse('<input type="text">');

        $result = $doc->apply(new SetAttribute('input', 'autocomplete', 'off'))->render();

        expect($result)->toContain('autocomplete="off"');
    });

    it('overwrites existing attribute', function (): void {
        $doc = $this->parse('<div data-id="old">content</div>');

        $result = $doc->apply(new SetAttribute('div', 'data-id', 'new'))->render();

        expect($result)->toBe('<div data-id="new">content</div>');
    });

    it('preserves bound :class sibling when setting attribute', function (): void {
        $doc = $this->parse('<div id="old" :class="$cls">content</div>');

        $result = $doc->apply(new SetAttribute('div', 'id', 'new'))->render();

        expect($result)->toContain('id="new"')
            ->and($result)->toContain(':class="$cls"');
    });

    it('preserves escaped ::style sibling when setting attribute', function (): void {
        $doc = $this->parse('<div data-x="1" ::style="raw">content</div>');

        $result = $doc->apply(new SetAttribute('div', 'data-x', '2'))->render();

        expect($result)->toContain('data-x="2"')
            ->and($result)->toContain('::style="raw"');
    });

    it('preserves Blade {{ }} attribute spread when setting attribute', function (): void {
        $doc = $this->parse('<div {{ $attributes->merge([\'class\' => "x"]) }}>content</div>');

        $result = $doc->apply(new SetAttribute('div', 'data-id', 'new'))->render();

        expect($result)->toContain('{{ $attributes->merge([\'class\' => "x"]) }}')
            ->and($result)->toContain('data-id="new"');
    });

    it('preserves Blade {{ }} attribute spread alongside static attributes', function (): void {
        $doc = $this->parse('<div class="anim" {{ $attributes }}>content</div>');

        $result = $doc->apply(new SetAttribute('div', 'data-id', 'new'))->render();

        expect($result)->toContain('class="anim"')
            ->and($result)->toContain('{{ $attributes }}')
            ->and($result)->toContain('data-id="new"');
    });

    it('preserves Blade {{ }} attribute spread across chained rewrites', function (): void {
        $doc = $this->parse('<div {{ $attributes->merge([\'class\' => "x"]) }}>content</div>');

        $result = $doc
            ->apply(new SetAttribute('div', 'data-first', '1'))
            ->apply(new SetAttribute('div', 'data-second', '2'))
            ->render();

        expect($result)->toContain('{{ $attributes->merge([\'class\' => "x"]) }}')
            ->and($result)->toContain('data-first="1"')
            ->and($result)->toContain('data-second="2"');
    });

    it('preserves multiple interleaved Blade constructs with attribute changes', function (): void {
        $doc = $this->parse('<div {{ $a }} name="x" {{ $b }} disabled {{ $c }}>content</div>');

        $result = $doc->apply(new SetAttribute('div', 'data-id', 'new'))->render();

        expect($result)->toBe('<div {{ $a }} name="x" {{ $b }} disabled {{ $c }} data-id="new">content</div>');
    });
});
