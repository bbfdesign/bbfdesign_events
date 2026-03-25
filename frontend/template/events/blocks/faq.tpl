<section class="bbf-faq-section">
    <div class="container">
        {if $title}<h3 class="mb-4">{$title}</h3>{/if}
        <div class="accordion" id="bbf-faq-block">
            {if !empty($faqItems)}
                {foreach $faqItems as $faq}
                    <div class="accordion-item">
                        <h4 class="accordion-header">
                            <button class="accordion-button{if !$faq@first} collapsed{/if}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faq-item-{$faq@index}">
                                {$faq.question}
                            </button>
                        </h4>
                        <div id="faq-item-{$faq@index}" class="accordion-collapse collapse{if $faq@first} show{/if}" data-bs-parent="#bbf-faq-block">
                            <div class="accordion-body">
                                {$faq.answer nofilter}
                            </div>
                        </div>
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>
</section>
