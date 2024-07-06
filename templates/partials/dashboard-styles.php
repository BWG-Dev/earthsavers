<style>

    body{
        font-size: 13px !important;
    }

    .es_customer_dashboard h5{
        font-weight: 800;
        font-size: 16px;
    }

    .es_user_list{
        background: black;
        color: white;
        position: absolute;
        width: 407px !important;
        border-radius: 8px !important;
        padding: 15px;
        display: none;
        z-index: 100;
    }

    #wpcontent{
        background: #ECF0F3 !important;
    }

    .es_user_list li:not(#es-no-item-loading){
        padding: 8px !important;
        border-bottom: 1px solid white !important;
    }

    .es_user_list li a{
        color: white !important;
        text-decoration: none !important;
    }

    .box-custom{
        box-shadow: 2px 1px 7px -3px rgba(237,220,220,0.75);
        -webkit-box-shadow: 2px 1px 7px -3px rgba(237,220,220,0.75);
        -moz-box-shadow: 2px 1px 7px -3px rgba(237,220,220,0.75);
        background: white !important;
        border: unset !important;

        border-radius: 8px;
    }

    .recent-activities-tbl tr{
        padding-bottom: 10px;
        border-bottom: 1px solid #ECF0F3;
        height: 50px;
    }


    .es_customer_avatar {
        height: 50px;
        width: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1px solid black;
        border-radius: 50%;
    }

    .recent-activities-tbl{
        width: 100%;
    }

    .recent-activities-tbl tr p a{
        text-decoration: none;
    }

    .recent-activities-tbl tr p {
        margin-bottom: 0;
    }

    .last-act-time{
        font-size: 11px;
        display: block;
    }

    .es_user_box{
        border: 1px solid lightgrey;
        border-radius: 5px;
    }

    .dashboard-user-name{
        text-decoration: none;
        color: black;
    }

    .es_advanced_view{
        float:right;
        color: black;
        text-decoration: none;
    }

    .es_section_scroll{
        height: 350px;
        overflow-y: scroll;
    }

    .stats-lineal-wrapper {
        height: 100%;
        justify-content: center;
        align-items: center;
    }

    .total_outstanding{
        font-size: 18px;
        font-weight: bold;
    }

    .label-line, .total-outstanding-subtext{
        color: lightgrey;
    }

    .total-line{
        cursor: pointer;
    }

    .total-line, .label-line, .total-outstanding-subtext{
        font-size: 10px;
    }
</style>
