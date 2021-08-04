<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{{ 'ItemsConfig'|get_plugin_lang('BuyCoursesPlugin') }}</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                {{ items_form }}
            </div>
        </div>
    </div>
</div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ 'FrequencyConfig'|get_plugin_lang('BuyCoursesPlugin') }}</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-5">
                    {{ frequency_form }}
                </div>
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>{{ 'Days'|get_plugin_lang('BuyCoursesPlugin') }}</th>
                                <th>{{ 'Price'|get_plugin_lang('BuyCoursesPlugin') }}</th>
                                <th>{{ 'Actions'|get_lang }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {% for frequency in frequencies %}
                                <tr>
                                    <td>{{ frequency.duration }}</td>
                                    <td>{{ frequency.price }}</td>
                                    <td>
                                        <a href="{{ _p.web_self ~ '?' ~ {'action':'delete_frequency', 'id': frequency.id}|url_encode() }}"
                                           class="btn btn-danger btn-sm">
                                            <em class="fa fa-remove"></em>
                                        </a>
                                    </td>
                                </tr>
                            {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

