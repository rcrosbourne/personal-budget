<?php

namespace Drupal\personal_budget_tracker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining an income source entity type.
 */
interface IncomeSourceInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
