<?php

namespace hypeJunction\Lists\Tests\Unit;

use hypeJunction\Lists\Filters\All;
use hypeJunction\Lists\Filters\CreatedBetween;
use hypeJunction\Lists\Filters\HasClosedMembership;
use hypeJunction\Lists\Filters\HasOpenMembership;
use hypeJunction\Lists\Filters\IsAdministeredBy;
use hypeJunction\Lists\Filters\IsContainedBy;
use hypeJunction\Lists\Filters\IsContainedByUsersGroups;
use hypeJunction\Lists\Filters\IsFeatured;
use hypeJunction\Lists\Filters\IsFriendOf;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FiltersTest extends TestCase {

    public function testAllFilterId() {
        $this->assertEquals('all', All::id());
    }

    public function testCreatedBetweenId() {
        $this->assertEquals('created_between', CreatedBetween::id());
    }

    public function testHasClosedMembershipId() {
        $this->assertEquals('has_closed_membership', HasClosedMembership::id());
    }

    public function testHasOpenMembershipId() {
        $this->assertEquals('has_open_membership', HasOpenMembership::id());
    }

    public function testIsAdministeredById() {
        $this->assertEquals('is_administered_by', IsAdministeredBy::id());
    }

    public function testIsContainedById() {
        $this->assertEquals('is_contained_by', IsContainedBy::id());
    }

    public function testIsContainedByUsersGroupsId() {
        $this->assertEquals('is_contained_by_users_groups', IsContainedByUsersGroups::id());
    }

    public function testIsFeaturedId() {
        $this->assertEquals('is_featured', IsFeatured::id());
    }

    public function testIsFriendOfId() {
        $this->assertEquals('is_friend_of', IsFriendOf::id());
    }
}
