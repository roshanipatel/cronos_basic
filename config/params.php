<?php

return [
	// this is used in contact page
    'adminEmail' => 'pask@open3s.com',
    // Link to ticketing system
    'ticket_url' => 'https://soporte.open3s.com/rt/Ticket/Display.html?id={ticket_id}',
    // CSV field separator
    'csv_separator' => ';',
    // Dir for assets
    'assets_dir' => '/var/www/cronos.open3s.int/assets',
    // Alert notifiers
    'alert_notifiers' => [
        'LogNotifier',
        'EmailNotifier',
    ],
    'mail' => [
            'from' => 'cronos@open3s.com',
            'subject' => '[CRONOS] Notificacion',
    ],
    // Default number of items in listings
	'default_page_size' => 20,
	// Project overview thresholds (in percentage)
	'project_progressbar' => array(70, 85),
];
