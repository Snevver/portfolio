<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hint Data Key to Method Mapping
    |--------------------------------------------------------------------------
    |
    | Maps data keys (used in hints.json) to their corresponding method names
    | in the HintDataService class.
    |
    */
    'key_to_method' => [
        'first_letter' => 'getFirstLetterOfGame',
        'developer' => 'getDeveloperOfGame',
        'publisher' => 'getPublisherOfGame',
        'tags' => 'getTagsOfGame',
        'scrambled_name' => 'getScramblednameOfGame',
        'playtime' => 'getPlaytimeOfGame',
        'release_date' => 'getReleaseDateOfGame',
        'banner_url' => 'getBannerUrlOfGame',
        'review_ratio' => 'getReviewRatioOfGame',
        'total_reviews' => 'getTotalReviewsOfGame',
        'required_disk_space' => 'getRequiredDiskSpaceOfGame',
        'last_played' => 'getLastPlayedOfGame',
        'total_owners' => 'getTotalOwnersOfGame',
        'current_players' => 'getCurrentPlayersOfGame',
    ],

    /*
    |--------------------------------------------------------------------------
    | Methods Requiring App ID
    |--------------------------------------------------------------------------
    |
    | Methods that require a Steam app ID to function. If the app ID is missing,
    | these methods will return null instead of being called.
    |
    */
    'requires_app_id' => [
        'getDeveloperOfGame',
        'getPublisherOfGame',
        'getTagsOfGame',
        'getReleaseDateOfGame',
        'getReviewRatioOfGame',
        'getTotalReviewsOfGame',
        'getRequiredDiskSpaceOfGame',
        'getTotalOwnersOfGame',
        'getCurrentPlayersOfGame',
    ],
];
