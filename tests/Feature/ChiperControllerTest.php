<?php

use App\Models\User;
use App\Models\Chirp;
use Illuminate\Support\Str;
use App\Events\ChirpCreated;
use Illuminate\Support\Facades\Event;
use App\Listeners\SendChirpCreatedNotifications;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('it should list all chirps with users order by created_at descending', function () {

    $userChirps = Chirp::factory(3)->create(['user_id' => $this->user->id]);

    $anotherUser = User::factory()->create();
    $anotherUserChirps = Chirp::factory(2)->create(['user_id' => $anotherUser]);

    $this->get(route('chirps.index'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Chirps/Index')
            ->has('chirps', 5)
            ->where('chirps.0.id', $anotherUserChirps->last()->id)
            ->has('chirps.0.user')
            ->where('chirps.0.user.id', $anotherUser->id)
            ->where('chirps.2.id', $userChirps->last()->id)
            ->has('chirps.2.user')
            ->where('chirps.2.user.id', $this->user->id)
        );
});

test('it should successfully store a chirp and trigger a created event', function () {
    Event::fake();
    
    $this->post(route('chirps.store'), ['message' => 'testing'])
        ->assertStatus(302)
        ->assertValid('message');

    $this->assertDatabaseCount('chirps', 1);
    $this->assertDatabaseHas('chirps', [
        'user_id' => $this->user->id,
        'message' => 'testing',
    ]);

    Event::assertDispatched(ChirpCreated::class);

    Event::assertListening(
        ChirpCreated::class,
        SendChirpCreatedNotifications::class
    );
});

test('it should not create a chirp if the message is invalid', function () {
    $this->post(route('chirps.store'))
        ->assertStatus(302)
        ->assertInvalid('message');

    $this->post(route('chirps.store'), ['message' => 2])
        ->assertStatus(302)
        ->assertInvalid('message');

    $this->post(route('chirps.store'), ['message' => Str::random(256)])
        ->assertStatus(302)
        ->assertInvalid('message');

    $this->assertDatabaseCount('chirps', 0);
});

test('it should update a chirp successfully if it is the same user that created it', function () {
    $chirp = Chirp::factory()->create(['user_id' => $this->user->id]);
    
    $this->put(route('chirps.update', ['chirp' => $chirp->id]), ['message' => 'testing 2'])
        ->assertStatus(302)
        ->assertValid('message');

    $this->assertDatabaseCount('chirps', 1);
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'user_id' => $this->user->id,
        'message' => 'testing 2',
    ]);
});

test('it should not update a chirp if the message is invalid', function () {
    $chirp = Chirp::factory()->create(['user_id' => $this->user->id]);
    
    $this->put(route('chirps.update', ['chirp' => $chirp->id]))
        ->assertStatus(302)
        ->assertInvalid('message');

    $this->put(route('chirps.update', ['chirp' => $chirp->id]), ['message' => 2])
        ->assertStatus(302)
        ->assertInvalid('message');

    $this->put(route('chirps.update', ['chirp' => $chirp->id]), ['message' => Str::random(256)])
        ->assertStatus(302)
        ->assertInvalid('message');

    $this->assertDatabaseCount('chirps', 1);
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'user_id' => $chirp->user_id,
        'message' => $chirp->message,
    ]);
});

test('it should not update a chirp successfully if it is not the same user who created it', function () {
    $chirp = Chirp::factory()->create();
    
    $this->put(route('chirps.update', ['chirp' => $chirp->id]), ['message' => 'testing 2'])
        ->assertStatus(403);

    $this->assertDatabaseCount('chirps', 1);
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'user_id' => $chirp->user_id,
        'message' => $chirp->message,
    ]);
});

test('it should successfully delete a chirp if it is the same user who created it', function () {
    $chirp = Chirp::factory()->create(['user_id' => $this->user->id]);
    
    $this->delete(route('chirps.destroy', ['chirp' => $chirp->id]))
        ->assertStatus(302);

    $this->assertDatabaseCount('chirps', 0);
    $this->assertModelMissing($chirp);
});

test('should not successfully delete a chirp if it is not the same user who created it', function () {
    $chirp = Chirp::factory()->create();
    
    $this->delete(route('chirps.destroy', ['chirp' => $chirp->id]))
        ->assertStatus(403);

    $this->assertDatabaseCount('chirps', 1);
    $this->assertModelExists($chirp);
});
