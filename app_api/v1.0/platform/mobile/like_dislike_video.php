<?php
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.playtubescript.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: wowondersocial@gmail.com   
// +------------------------------------------------------------------------+
// | PlayTube - The Ultimate Video Sharing Platform
// | Copyright (c) 2017 PlayTube. All rights reserved.
// +------------------------------------------------------------------------+


if (!IS_LOGGED) {

    $response_data    = array(
        'api_status'  => '400',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '1',
            'error_text' => 'Not logged in'
        )
    );
}

else if (empty($_POST['video_id']) || !is_numeric($_POST['video_id'])) {

	$response_data    = array(
	    'api_status'  => '400',
	    'api_version' => $api_version,
	    'errors' => array(
            'error_id' => '2',
            'error_text' => 'Bad Request, Invalid or missing parameter'
        )
	);
	
}

else if (empty($_POST['action']) || !in_array($_POST['action'], array('like','dislike','up','down'))) {

	$response_data    = array(
	    'api_status'  => '400',
	    'api_version' => $api_version,
	    'errors' => array(
            'error_id' => '2',
            'error_text' => 'Bad Request, Invalid or missing parameter'
        )
	);

}

else{

    $id    = PT_Secure($_POST['video_id']);
    $video = $db->where('id', $id)->getValue(T_VIDEOS, 'count(*)');
    $vote  = PT_Secure($_POST['action']);

    if (!empty($video)) {

        $response_data    = array(
            'api_status'  => '200',
            'api_version' => $api_version,
        );

        if ($vote == 'like' || $vote == 'up') {

            $db->where('user_id', $user->id);
            $db->where('video_id', $id);
            $db->where('type', 1);
            $check_for_like = $db->getValue(T_DIS_LIKES, 'count(*)');

            if ($check_for_like > 0) {
                $db->where('user_id', $user->id);
                $db->where('video_id', $id);
                $db->where('type', 1);

                $delete = $db->delete(T_DIS_LIKES);
                $response_data['success_type'] = 'deleted_like';
            } 

            else {

                $db->where('user_id', $user->id);
                $db->where('video_id', $id);
                $db->where('type', 2);

                $check_for_dislike = $db->getValue(T_DIS_LIKES, 'count(*)');

                if ($check_for_dislike) {
                    $db->where('user_id', $user->id);
                    $db->where('video_id', $id);
                    $db->where('type', 2);
                    $delete = $db->delete(T_DIS_LIKES);
                }

                $insert_data   = array(
                    'user_id'  => $user->id,
                    'video_id' => $id,
                    'time'     => time(),
                    'type'     => 1
                );

                $insert        = $db->insert(T_DIS_LIKES, $insert_data);

                if ($insert) {
                    $response_data['success_type'] = 'added_like';
                }
            }
        } 

        else if ($vote == 'dislike' || $vote == 'down') {

            $db->where('user_id', $user->id);
            $db->where('video_id', $id);
            $db->where('type', 2);
            $check_for_like = $db->getValue(T_DIS_LIKES, 'count(*)');

            if ($check_for_like > 0) {
                $db->where('user_id', $user->id);
                $db->where('video_id', $id);
                $db->where('type', 2);
                $delete = $db->delete(T_DIS_LIKES);
                $response_data['success_type'] = 'deleted_dislike';
            } 

            else {

                $db->where('user_id', $user->id);
                $db->where('video_id', $id);
                $db->where('type', 1);
                $check_for_dislike = $db->getValue(T_DIS_LIKES, 'count(*)');

                if ($check_for_dislike) {
                    $db->where('user_id', $user->id);
                    $db->where('video_id', $id);
                    $db->where('type', 1);
                    $delete = $db->delete(T_DIS_LIKES);
                }

                $insert_data   = array(
                    'user_id'  => $user->id,
                    'video_id' => $id,
                    'time'     => time(),
                    'type'     => 2
                );

                $insert        = $db->insert(T_DIS_LIKES, $insert_data);

                if ($insert) {
                    $response_data['success_type'] = 'added_dislike';
                }
            }
        }
    }

    else{
        $response_data       = array(
            'api_status'     => '404',
            'api_version'    => $api_version,
            'errors'         => array(
                'error_id'   => '2',
                'error_text' => 'Video does not exist'
            )
        );
    }
}
