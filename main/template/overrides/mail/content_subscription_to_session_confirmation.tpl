{% if 'mutua.apacampus.com' in _p.web %}
<p>{{ complete_name }},</p>
<p>Mutualiko Campusean erregistratuta zaude, honako parametro hauekin:</p>
{{ username ? '<p>' ~ username ~ '</p>' }}
<p>Eskerrik asko gure campus birtualean izena emateagatik</p>
<p style="color: #ff0000;">Sartu ezin baduzu, mutualia onartu arte itxaron beharko duzu</p>
<p>Mutualiko Campusean: {{ _p.web }}</p>
{{ lostPassword ? '<p>' ~ lostPassword ~ '</p>' }}
<p>Arazorik baduzu, zalantzarik gabe jarri gurekin harremanetan</p>
<p>{{ 'SignatureFormula'|get_lang }}</p>
<p>{{ _admin.name }} {{ _admin.surname }}<br>
    Kudeatzailea Mutualiko Campus Birtuala<br>
    {{ _admin.telephone ? 'T. ' ~ _admin.telephone }}<br>
    {{ _admin.email ? 'Email'|get_lang ~ ': ' ~ _admin.email }}</p>
{% else %}
<p>{{ 'Dear'|get_lang }} {{ complete_name }},</p>
<p>{{ 'YouAreRegisterToSessionX'|get_lang|format(session_name) }}</p>
{{ username ? '<p>' ~ username ~ '</p>' }}
<p>{{ 'Address'|get_lang }}  {{ _s.site_name }} {{ 'Is'|get_lang }} : {{ _p.web }}</p>
{{ lostPassword ? '<p>' ~ lostPassword ~ '</p>' }}
<p>{{ 'SignatureFormula'|get_lang }}</p>
<p>{{ _admin.name }} {{ _admin.surname }}<br>
    {{ 'Manager'|get_lang }} {{ _s.site_name }}<br>
    {{ _admin.telephone ? 'T. ' ~ _admin.telephone }}<br>
    {{ _admin.email ? 'Email'|get_lang ~ ': ' ~ _admin.email }}</p>
{% endif %}
