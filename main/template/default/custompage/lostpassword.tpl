<div class="custompage">
    <div class="limiter">
        <div class="container-login">
            <div class="wrap-login width-register">
                <div class="logo">
                    <img width="250px" class="img-responsive" title="{{ _s.site_name }}" src="{{ _p.web_css_theme }}images/header-logo.svg">
                </div>
                {% if info %}
                    <div class="alert alert-info">
                        {{ info }}
                    </div>
                    <div class="text-center" style="margin-top: 20px;">
                        <a href="{{_p.web}}" class="btn btn-primary btn-block">
                            {{ "BackToHomePage" |get_lang }}
                        </a>
                    </div>
                {% endif %}
                {% if error %}
                    <div class="alert alert-danger">
                        {{ error }}
                    </div>
                {% endif %}
                {% if not info %}
                    {{ form }}
                {% endif %}
                <div class="software-name">
                    <a href="{{_p.web}}" target="_blank">
                        {{ "PoweredByX" |get_lang | format(_s.software_name) }}
                    </a>&copy; {{ "now"|date("Y") }}
                </div>
            </div>
        </div>
    </div>
</div>
