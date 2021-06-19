{% extends 'layout/layout_1_col.tpl'|get_template %}
{% import 'default/macro/macro.tpl' as display %}

{% block content %}
    <style>
        #listFriends .list-group {
            max-height: 250px;
            overflow-y:auto;
        }
    </style>
    <div class="row">
        <div class="col-md-3 sidebar">
            {{ social_avatar_block }}

            <div class="social-network-menu">
            {{ social_menu_block }}
            </div>
            
            <!-- BLOCK HELP -->
            {% if help_block %}
                {{ display.collapse('help', 'MenuGeneral'|get_lang, help_block, true) }}
            {% endif %}
        </div>
        <div class="col-md-9">
            {{ content }}
        </div>
    </div>
{% endblock %}
