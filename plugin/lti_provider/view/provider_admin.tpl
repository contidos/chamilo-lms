{{ 'LtiProviderDescription'|get_plugin_lang('LtiProviderPlugin') }}


    <div class="btn-toolbar">
        <a href="{{ _p.web_plugin }}lti_provider/provider_settings.php" class="btn btn-link pull-right ajax" data-title="{{ 'ConnectionDetails'|get_plugin_lang('LtiProviderPlugin') }}">
            <span class="fa fa-cogs fa-fw" aria-hidden="true"></span> {{ 'ConnectionDetails'|get_plugin_lang('LtiProviderPlugin') }}
        </a>
        <a href="{{ _p.web_plugin }}lti_provider/stats.php" class="btn btn-link pull-right">
            <span class="fa fa-tachometer fa-fw" aria-hidden="true"></span> {{ 'Stats'|get_lang }}
        </a>
        <a href="{{ _p.web_plugin }}lti_provider/create.php" class="btn btn-primary">
            <span class="fa fa-plus fa-fw" aria-hidden="true"></span> {{ 'AddPlatform'|get_plugin_lang('LtiProviderPlugin') }}
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover admin-tools">
            <thead>
                <tr>
                    <th>{{ 'Name'|get_lang }}</th>
                    <th>{{ 'AvailableLicenses'|get_plugin_lang('LtiProviderPlugin') }}</th>
                    <th>{{ 'PlatformName'|get_plugin_lang('LtiProviderPlugin') }}</th>
                    <th>{{ 'ClientId'|get_plugin_lang('LtiProviderPlugin') }}</th>
                    <th class="text-center">{{ 'DeploymentId'|get_plugin_lang('LtiProviderPlugin') }}</th>
                    <th class="text-center">{{ 'URLs'|get_plugin_lang('LtiProviderPlugin') }}</th>
                    <th class="text-center">{{ 'ToolProvider'|get_plugin_lang('LtiProviderPlugin') }}</th>
                    <th class="text-right">{{ 'Actions'|get_lang }}</th>
                </tr>
            </thead>
            <tbody>
            {% for platform in platforms %}
            {% set url_params = {'id': platform.getId}|url_encode() %}
                <tr>
                    <td>{{ platform.getName }}</td>
                    <td>
                        {% if platform.getTotalLicenses == 0 %}
                            <span style="color: #000000;">∞</span>
                        {% else %}
                            {% set percentage = (platform.getAvailableLicenses / platform.getTotalLicenses) * 100 %}
                            {% if platform.getAvailableLicenses == 0 %}
                                <span style="color: #ff0000;">{{ platform.getAvailableLicenses }}</span>
                            {% elseif percentage <= 15 %}
                                <span style="color: #ffa500;">{{ platform.getAvailableLicenses }}</span>
                            {% elseif percentage >= 50 %}
                                <span style="color: #008000;">{{ platform.getAvailableLicenses }}</span>
                            {% else %}
                                <span style="color: #ffa500;">{{ platform.getAvailableLicenses }}</span>
                            {% endif %}
                        {% endif %}
                    </td>
                    <td>{{ platform.getIssuer }}</td>
                    <td>{{ platform.getClientId }}</td>
                    <td>{{ platform.getDeploymentId }}</td>
                    <td>
                        <p><strong>{{ 'AuthLoginUrl'|get_plugin_lang('LtiProviderPlugin') }}:</strong><br> {{ platform.getAuthLoginUrl }}</p>
                        <p><strong>{{ 'AuthTokenUrl'|get_plugin_lang('LtiProviderPlugin') }}:</strong><br> {{ platform.getAuthTokenUrl }}</p>
                        <p><strong>{{ 'KeySetUrl'|get_plugin_lang('LtiProviderPlugin') }}:</strong><br> {{ platform.getKeySetUrl }}</p>
                    </td>
                    <td>{{ platform.getToolProvider }}</td>
                    <td>
                        <a href="{{ _p.web_plugin }}lti_provider/edit.php?{{ url_params }}">
                            {{ 'edit.png'|img(22, 'Edit'|get_lang) }}
                        </a>
                        <a href="{{ _p.web_plugin }}lti_provider/licenses.php?client_id={{ platform.getClientId }}">
                            {{ 'key.png'|img(22, 'Licencias'|get_lang) }}
                        </a>
                        <a href="{{ _p.web_plugin }}lti_provider/delete.php?{{ url_params }}">
                            {{ 'delete.png'|img(22, 'Delete'|get_lang) }}
                        </a>
                    </td>
                </tr>
            {% endfor %}

            </tbody>
        </table>
    </div>

