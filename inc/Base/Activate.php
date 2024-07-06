<?php

/*
*
* @package yariko
*
*/
namespace Es\Inc\Base;

class Activate{

    public static function activate(){

        //Set the Business Account Role

        add_role(
            'business',
            'Business Account',
            array(
                'read'         => true,
                'delete_posts' => false
            )
        );

        add_role(
            'sub-account',
            'Sub-Account',
            array(
                'read'         => true,
                'delete_posts' => false
            )
        );

	    add_role(
		    'residential-4',
		    'Res 4',
		    array(
			    'read'         => true,
			    'delete_posts' => false
		    )
	    );

	    add_role(
		    'need_to_renew',
		    'Need to Renew',
		    array(
			    'read'         => true,
			    'delete_posts' => false
		    )
	    );

		add_role(
			'business-4',
			'New Business',
			array(
				'read'         => true,
				'delete_posts' => false
			)
		);
    }


}
