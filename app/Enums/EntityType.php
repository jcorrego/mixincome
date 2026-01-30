<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Entity types for legal entities under a user profile.
 *
 * @link https://mixincome.test/docs/enums/entity-type
 */
enum EntityType: string
{
    case LLC = 'LLC';
    case SCorp = 'SCorp';
    case CCorp = 'CCorp';
    case Partnership = 'Partnership';
    case Trust = 'Trust';
    case Other = 'Other';
}
