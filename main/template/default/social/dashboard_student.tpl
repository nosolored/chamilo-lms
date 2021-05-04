{% extends 'layout/layout_1_col.tpl'|get_template %}

{% block content %}
    <style>
        #listFriends .list-group {
            max-height: 250px;
            overflow-y:auto;
        }
    </style>
    <div class="row">
        <div class="col-md-3">
            {{ social_avatar_block }}

            <div class="social-network-menu">
            {{ social_menu_block }}
            </div>
        </div>
        <div class="col-md-9">
            {{ content }}
        </div>
    </div>
{% endblock %}
