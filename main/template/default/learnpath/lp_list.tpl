{% if data_lp_list is not empty %}
<div id="learning_path_toc" class="scorm-list">
    <div class="scorm-title">
        <h4>{{ course_title }}</h4>
    </div>
    <hr style="margin: -5px 0px 10px 0px;">
    <div class="scorm-body">
        <div id="inner_lp_toc" class="inner_lp_toc scrollbar-light">
            {% for lp_data in data_lp_list %}
                <!-- new view block accordeon -->
                {% if lp_data.category.id == 0 %}
                <div id="not-category" class="panel panel-default">
                    <div class="panel-body">
                        {% for row in lp_data.lp_list %}
                            <div class="lp-item">
                                <div class="row">
                                    <div class="col-md-9">
                                        {% if row.lp_id == lp_id %}
                                            <i class="fa fa-chevron-circle-right text-primary" aria-hidden="true"></i>
                                            <a href="{{ row.url_start }}">
                                                <strong>
                                                    {{ row.title }}
                                                    {{ row.session_image }}
                                                </strong>
                                            </a>
                                        {% else %}
                                            <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
                                            <a href="{{ row.url_start }}">
                                                {{ row.title }}
                                                {{ row.session_image }}
                                            </a>
                                        {% endif %}
                                    </div>
                                    
                                    <div class="col-md-3">
                                        {% if row.lp_id == lp_id %}
                                            <div style="height: 20px; margin-bottom: 20px;text-align: center;">
                                                <strong>({{ 'InCourse' | get_lang | capitalize }})</strong>
                                            </div>
                                        {% else %}
                                            {{ row.dsp_progress }}
                                        {% endif %}
                                    </div>
                                     
                                </div>
                            </div>
                        {% endfor %}
                    </div>
                </div>
                {% endif %}

                {% if categories|length > 1 and lp_data.category.id %}
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="heading-{{ lp_data.category.getId() }}">
                             <h4 class="panel-title">
                                <a role="button" data-toggle="collapse" data-parent="#lp-accordion"
                                   href="#collapse-{{ lp_data.category.getId() }}" aria-expanded="true"
                                   aria-controls="collapse-{{ lp_data.category.getId() }}">
                                    {{ lp_data.category.getName() }}
                                </a>
                            </h4>
                        </div>
                        {% set number = number + 1 %}
                        <div id="collapse-{{ lp_data.category.getId() }}" class="collapse {{ (number == 1 ? 'in':'') }}"
                             role="tabpanel" aria-labelledby="heading-{{ lp_data.category.getId() }}">
                            <div class="panel-body">
                                {% if lp_data.lp_list %}
                                    {% for row in lp_data.lp_list %}
                                        <div class="lp-item">
                                            <div class="row">
                                                <div class="col-md-9">
                                                    {% if row.lp_id == lp_id %}
                                                    <i class="fa fa-chevron-circle-right text-primary" aria-hidden="true"></i>
                                                    <a href="{{ row.url_start }}">
                                                        <strong>
                                                            {{ row.title }}
                                                            {{ row.session_image }}
                                                        </strong>
                                                    </a>
                                                    {% else %}
                                                    <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
                                                    <a href="{{ row.url_start }}">
                                                        {{ row.title }}
                                                        {{ row.session_image }}
                                                    </a>
                                                    {% endif %}
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    {% if row.lp_id == lp_id %}
                                                        <div style="height: 20px; margin-bottom: 20px;text-align: center;">
                                                            <strong>({{ 'InCourse' | get_lang | capitalize }})</strong>
                                                        </div>
                                                    {% else %}
                                                        {{ row.dsp_progress }}
                                                    {% endif %}
                                                </div>
                                                 
                                            </div>
                                        </div>
                                    {% endfor %}
                                {% endif %}
                            </div>
                        </div>
                    </div>
                {% endif %}
                <!-- end view block accordeon -->
            {% endfor %}
        </div>
    </div>
</div>
{% endif %}
