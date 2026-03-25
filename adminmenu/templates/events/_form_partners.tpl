<div class="card">
    <div class="card-header"><strong>Partner zuordnen</strong></div>
    <div class="card-body">
        {if !empty($allPartners)}
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th width="40"></th><th>Partner</th><th>Kategorie beim Event</th></tr></thead>
                    <tbody>
                        {foreach $allPartners as $p}
                            <tr>
                                <td>
                                    <input class="form-check-input" type="checkbox"
                                           name="event_partners[{$p->id}][partner_id]" value="{$p->id}"
                                           {if in_array($p->id, $assignedPartnerIds|default:[])}checked{/if}>
                                </td>
                                <td>
                                    {if $p->logo}<img src="{$p->logo}" alt="" style="max-height:24px;margin-right:0.5rem;">{/if}
                                    {$p->name|default:$p->slug}
                                </td>
                                <td>
                                    <select name="event_partners[{$p->id}][category_id]" class="form-select form-select-sm" style="max-width:200px;">
                                        <option value="">Keine</option>
                                        {foreach $partnerCategories|default:[] as $pc}
                                            <option value="{$pc->id}">{$pc->name|default:$pc->slug}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            <p class="text-muted">Keine Partner vorhanden. <a href="../partners?action=create">Partner erstellen</a></p>
        {/if}
    </div>
</div>
