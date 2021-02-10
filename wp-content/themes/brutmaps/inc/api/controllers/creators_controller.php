<?php

// - GET /creator/$id
function API_GET_CREATOR_BY_ID( $data ) {
    $creatorID = intval($data['id']);
    $creatorSmallData = getCreatorsSmallDataByIDs([$creatorID])[0];

    // Error handling
    if (is_null($creatorSmallData)) {
        return failureResponse('Creator does not exist');
    }

    $creatorSmallData['sights'] = getSightsToWhichRelatedArchitectWithID($creatorID);;
    return successResponse($creatorSmallData);
}

// HELPERS

function getSightsToWhichRelatedArchitectWithID($ID) {

    $sightsArgh = array(
        'numberposts'	=> -1,
        'post_type'		=> 'sight',
        'fields'        => 'ids',
        'meta_query'	=> array(
            'relation'  => 'OR',
            array(
                'key'		=> 'choose_associated_people',
                'value'		=> $ID,
                'compare'	=> 'LIKE'
            ),
            array(
                'key'		=> 'choose_architects',
                'value'		=> $ID,
                'compare'	=> 'LIKE'
            )
        )
    );

    $ids = get_posts($sightsArgh);

    return getSightsSmallDataByIDs($ids);
}
