<?php

namespace Corcel\Tests\Unit\Model;

use Carbon\Carbon;
use Corcel\Model\Collection\MetaCollection;
use Corcel\Model\User;

/**
 * Class UserTest
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class UserTest extends \Corcel\Tests\TestCase
{
    /**
     * @test
     */
    public function it_is_instance_of_user()
    {
        $user = factory(User::class)->create();

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @test
     */
    public function it_has_the_correct_id()
    {
        $user = factory(User::class)->create(['ID' => 20]);

        $this->assertNotNull($user);
        $this->assertEquals(20, $user->ID);
    }

    /**
     * @test
     */
    public function it_can_be_ordered()
    {
        $date = Carbon::now()->subYear();

        $first = factory(User::class)->create(['user_registered' => $date]);
        $last = factory(User::class)->create(['user_registered' => $date->addMonth()]);

        $newest = User::newest()->first();
        $oldest = User::oldest()->first();

        $this->assertEquals($first->ID, $oldest->ID);
        $this->assertEquals($last->ID, $newest->ID);
    }

    /**
     * @test
     */
    public function it_has_multiple_property_aliases()
    {
        $user = factory(User::class)->create();
        $user->saveMeta('nickname', 'foo');
        $user->saveMeta('first_name', 'bar');
        $user->saveMeta('last_name', 'baz');

        $this->assertEquals($user->last_name, 'baz');
        $this->assertEquals($user->user_login, $user->login);
        $this->assertEquals($user->user_email, $user->email);
        $this->assertEquals($user->user_nicename, $user->slug);
        $this->assertEquals($user->user_url, $user->url);
        $this->assertEquals($user->meta->nickname, $user->nickname);
        $this->assertEquals($user->meta->first_name, $user->first_name);
        $this->assertEquals($user->meta->last_name, $user->last_name);
        $this->assertEquals($user->user_registered, $user->created_at);
    }

    /**
     * @test
     */
    public function it_has_the_correct_auth_identifier()
    {
        $user = factory(User::class)->create();

        $this->assertEquals($user->ID, $user->getAuthIdentifier());
    }

    /**
     * @test
     */
    public function it_can_add_meta()
    {
        $user = factory(User::class)->create();

        $user->saveMeta('foo', 'bar');

        $this->assertNotEmpty($user->meta);
        $this->assertNotEmpty($user->fields);
        $this->assertInstanceOf(MetaCollection::class, $user->meta);
    }

    /**
     * @test
     */
    public function it_can_update_meta()
    {
        $user = factory(User::class)->create();

        $user->saveMeta('foo', 'bar');
        $user->saveField('foo', 'baz');

        $this->assertEquals($user->meta->foo, 'baz');
    }

    /**
     * @test
     */
    public function it_can_update_multiples_metas()
    {
        $user = factory(User::class)->create();

        $user->createMeta(['foo' => 'bar', 'fee' => 'baz']);

        $this->assertEquals('bar', $user->meta->foo);
        $this->assertEquals('baz', $user->meta->fee);

        $user->saveMeta(['foo' => 'baz', 'fee' => 'bar']);

        $this->assertEquals('baz', $user->meta->foo);
        $this->assertEquals('bar', $user->meta->fee);
    }

    /**
     * @test
     */
    public function it_can_have_a_different_database_connection()
    {
        $user = factory(User::class)->make();
        $user->setConnection('foo');
        $user->save();

        $user->createMeta('fee', 'baz');

        $this->assertEquals('foo', $user->getConnectionName());

        $user->meta->each(function ($meta) {
            $this->assertEquals('foo', $meta->getConnectionName());
        });
    }

    /**
     * @test
     */
    public function it_has_meta_scope_with_empty_meta()
    {
        $id = factory(User::class)->create()->ID;

        $user = (new User())->newQuery()
            ->where('ID', $id)
            ->hasMeta('foo', 'bar')
            ->first();

        $this->assertEmpty($user);
    }

    /**
     * @test
     */
    public function it_has_meta_scope_with_valid_meta()
    {
        $user = factory(User::class)->create();
        $user->saveMeta('foo', 'bar');

        $validUser = (new User())->newQuery()
            ->where('ID', $user->ID)
            ->hasMeta('foo', 'bar')
            ->first();

        $this->assertNotEmpty($validUser);
    }
}
