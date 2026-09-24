<?php

declare(strict_types=1);

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.storefront', [
                'metaDescription' => __('seo.faq_description'),
                'canonicalUrl' => route('faq'),
            ])
            ->title(__('seo.faq_title'));
    }
};
