<?php

namespace Orion\Tests\Feature\Relations\HasMany;

use Illuminate\Support\Facades\Gate;
use Mockery;
use Orion\Contracts\ComponentsResolver;
use Orion\Tests\Feature\TestCase;
use Orion\Tests\Fixtures\App\Http\Resources\SampleResource;
use Orion\Tests\Fixtures\App\Models\Company;
use Orion\Tests\Fixtures\App\Models\Post;
use Orion\Tests\Fixtures\App\Models\Team;
use Orion\Tests\Fixtures\App\Models\User;
use Orion\Tests\Fixtures\App\Policies\GreenPolicy;
use Orion\Tests\Fixtures\App\Policies\RedPolicy;

class HasManyRelationStandardDeleteOperationsTest extends TestCase
{
    /** @test */
    public function trashing_a_single_soft_deletable_relation_resource_without_authorization()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create(['user_id' => $user->id]);

        Gate::policy(Post::class, RedPolicy::class);

        $response = $this->delete("/api/users/{$user->id}/posts/{$post->id}");

        $this->assertUnauthorizedResponse($response);
    }

    /** @test */
    public function deleting_a_single_relation_resource_without_authorization()
    {
        $company = factory(Company::class)->create();
        $team = factory(Team::class)->create(['company_id' => $company->id]);

        Gate::policy(Team::class, RedPolicy::class);

        $response = $this->delete("/api/companies/{$company->id}/teams/{$team->id}");

        $this->assertUnauthorizedResponse($response);
    }

    /** @test */
    public function force_deleting_a_single_relation_resource_without_authorization()
    {
        $user = factory(User::class)->create();
        $trashedPost = factory(Post::class)->state('trashed')->create(['user_id' => $user->id]);

        Gate::policy(Post::class, RedPolicy::class);

        $response = $this->delete("/api/users/{$user->id}/posts/{$trashedPost->id}?force=true");

        $this->assertUnauthorizedResponse($response);
    }

    /** @test */
    public function trashing_a_single_soft_deletable_relation_resource_when_authorized()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create(['user_id' => $user->id]);

        Gate::policy(Post::class, GreenPolicy::class);

        $response = $this->delete("/api/users/{$user->id}/posts/{$post->id}");

        $this->assertResourceTrashed($response, $post);
    }

    /** @test */
    public function deleting_a_single_relation_resource_when_authorized()
    {
        $company = factory(Company::class)->create();
        $team = factory(Team::class)->create(['company_id' => $company->id])->fresh();

        Gate::policy(Team::class, GreenPolicy::class);

        $response = $this->delete("/api/companies/{$company->id}/teams/{$team->id}");

        $this->assertResourceDeleted($response, $team);
    }

    /** @test */
    public function force_deleting_a_single_trashed_relation_resource_when_authorized()
    {
        $user = factory(User::class)->create();
        $trashedPost = factory(Post::class)->state('trashed')->create(['user_id' => $user->id])->fresh();

        Gate::policy(Post::class, GreenPolicy::class);

        $response = $this->delete("/api/users/{$user->id}/posts/{$trashedPost->id}?force=true");

        $this->assertResourceDeleted($response, $trashedPost);
    }

    /** @test */
    public function deleting_a_single_trashed_relation_resource_without_trashed_query_parameter()
    {
        $user = factory(User::class)->create();
        $trashedPost = factory(Post::class)->state('trashed')->create(['user_id' => $user->id]);

        Gate::policy(Post::class, GreenPolicy::class);

        $response = $this->delete("/api/users/{$user->id}/posts/{$trashedPost->id}");

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('posts', $trashedPost->getAttributes());
    }

    /** @test */
    public function deleting_a_single_trashed_relation_resource_with_trashed_query_parameter()
    {
        $user = factory(User::class)->create();
        $trashedPost = factory(Post::class)->state('trashed')->create(['user_id' => $user->id]);

        Gate::policy(Post::class, GreenPolicy::class);

        $response = $this->delete("/api/users/{$user->id}/posts/{$trashedPost->id}?with_trashed=true");

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('posts', $trashedPost->getAttributes());
    }

    /** @test */
    public function deleting_a_single_trashed_relation_resource_with_force_query_parameter()
    {
        $user = factory(User::class)->create();
        $trashedPost = factory(Post::class)->state('trashed')->create(['user_id' => $user->id])->fresh();

        Gate::policy(Post::class, GreenPolicy::class);

        $response = $this->delete("/api/users/{$user->id}/posts/{$trashedPost->id}?force=true");

        $this->assertResourceDeleted($response, $trashedPost);
    }

    /** @test */
    public function transforming_a_single_deleted_relation_resource()
    {
        $company = factory(Company::class)->create();
        $team = factory(Team::class)->create(['company_id' => $company->id])->fresh();

        app()->bind(ComponentsResolver::class, function () {
            $componentsResolverMock = Mockery::mock(\Orion\Drivers\Standard\ComponentsResolver::class)->makePartial();
            $componentsResolverMock->shouldReceive('resolveResourceClass')->once()->andReturn(SampleResource::class);

            return $componentsResolverMock;
        });

        Gate::policy(Team::class, GreenPolicy::class);

        $response = $this->delete("/api/companies/{$company->id}/teams/{$team->id}");

        $this->assertResourceDeleted($response, $team, ['test-field-from-resource' => 'test-value']);
    }

    /** @test */
    public function deleting_a_single_relation_resource_and_getting_included_relation()
    {
        $company = factory(Company::class)->create()->fresh();
        $team = factory(Team::class)->create(['company_id' => $company->id])->fresh();

        Gate::policy(Team::class, GreenPolicy::class);

        $response = $this->delete("/api/companies/{$company->id}/teams/{$team->id}?include=company");

        $this->assertResourceDeleted($response, $team, ['company' => $company->toArray()]);
    }
}