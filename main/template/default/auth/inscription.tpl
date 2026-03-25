{%
    extends hide_header == true
    ? 'layout/blank.tpl'|get_template
    : 'layout/layout_1_col.tpl'|get_template
%}

{% block content %}

<style>
.help-registration .alert {
    margin-bottom: 0 !important;
}
</style>

{{ inscription_header }}
{{ inscription_content }}
{{ form }}
{{ text_after_registration }}

{% endblock %}
    