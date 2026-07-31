<div class="page-header">
    <h3>{{ platform.getName }}</h3>
</div>

<div class="table-responsive">
    {% if table %}
        {{ table }}
    {% else %}
        <div class="alert alert-warning">
            {{ 'NoLicensesFound'|get_plugin_lang('LtiProviderPlugin') }}
        </div>
    {% endif %}
</div> 
