{% extends 'layout/page.tpl'|get_template %}
{% import 'default/macro/macro.tpl' as display %}
{% set sidebar_hide = 'sidebar_hide'|api_get_configuration_value %}

{% block body %}
    {% if plugin_main_top %}
        {{ display.pluginPanel('main-top', plugin_main_top) }}
    {% endif %}
    <div class="row">
        <div class="col-md-7">
            <div class="page-content">
                {% if plugin_content_top %}
                    <div class="page-content-top">
                        {{ plugin_content_top }}
                    </div>
                {% endif %}

                {{ sniff_notification }}

                {% block page_body %}
                    {% include 'layout/page_body.tpl'|get_template %}
                {% endblock %}

                {% if home_welcome %}
                    <article id="home-welcome">
                        {{ home_welcome }}
                    </article>
                {% endif %}

                {% if home_include %}
                <article id="home-include">
                    {{ home_include }}
                </article>
                {% endif %}


                {% if welcome_to_course_block %}
                    <article id="homepage-course">
                        {{ welcome_to_course_block }}
                    </article>
                {% endif %}

                {% block content %}
                    {% if content is not null %}
                        <section id="page" class="{{ course_history_page }}">
                            {{ content }}
                        </section>
                    {% endif %}
                {% endblock %}

                {% if announcements_block %}
                    <article id="homepage-announcements">
                        {{ announcements_block }}
                    </article>
                {% endif %}

                {% if course_category_block %}
                    <article id="homepage-course-category">
                        {{ course_category_block }}
                    </article>
                {% endif %}
                {% include 'layout/hot_courses.tpl'|get_template %}
                {% include 'session/sessions_current.tpl'|get_template %}
                {% if plugin_content_bottom %}
                    <div id="plugin_content_bottom">
                        {{ plugin_content_bottom }}
                    </div>
                {% endif %}
            </div>
        </div>
        <div class="col-md-5">
            {{ meeting_list }}
        </div>
    </div>
    {% if plugin_main_bottom %}
        {{ display.pluginPanel('main-bottom', plugin_main_bottom) }}
    {% endif %}
{% endblock %}
