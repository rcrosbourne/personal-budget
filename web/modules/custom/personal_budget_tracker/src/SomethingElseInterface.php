<?php

namespace Drupal\personal_budget_tracker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a something else entity type.
 */
interface SomethingElseInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
