<?php

return [
    'reregistration' => [
        'success' => 'Participant re-registration has been recorded.',
        'already' => 'The participant has already re-registered.',
        'token_invalid' => 'The attendance QR token is not recognised.',
        'event_mismatch' => 'The participant registration does not belong to this event.',
        'not_registered' => 'The participant registration status is not Registered.',
        'online_rejected' => 'Re-registration is only for on-site participants. Online participants record attendance with the session QR.',
        'payment_unverified' => 'The participant payment has not been verified.',
        'first_day_only' => 'Re-registration is only open on the first day of the event.',
    ],
    'payment' => [
        'addons_idr_only' => 'Add-on services are only available for IDR fees.',
        'presenter_abstract_not_accepted' => 'Presenter payment proof can only be uploaded after the abstract has been accepted.',
    ],
    'attendance' => [
        'qr_offline_only' => 'The personal attendance QR is only for on-site participants. Online participants record attendance with the session QR.',
        'not_registered' => 'The registration status is not Registered.',
        'payment_unverified' => 'The attendance QR is available after the payment is verified.',
    ],
];
