<?php

declare(strict_types=1);

return [
    ['GET', '/login', 'AuthController@login'],
    ['POST', '/logout', 'AuthController@logout'],
];
