<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function fakeAuthentication() {
        Sanctum::actingAs(
            $user = factory(User::class)->create(),
            ['*']
        );

        return $user->id;
    }

    public function testProductRoutesAreAuthProtected() {
        $response = $this->get('/api/products/1');

        $response->assertStatus(401);
    }

    public function testProductCreationValidationIsFunctional() {
        $this->fakeAuthentication();

        $response = $this->post('/api/products');

        $response->assertStatus(422);
    }

    public function testProductUpdateValidationIsFunctional() {
        $product = factory(Product::class)->create();

        $this->fakeAuthentication();

        $response = $this->put("/api/products/{$product->id}");

        $response->assertStatus(422);
    }

    public function testProductImageUploadValidationIsFunctional() {
        $product = factory(Product::class)->create();

        $this->fakeAuthentication();

        $response = $this->patch("/api/products/{$product->id}/upload-image");

        $response->assertStatus(422);
    }

    public function testProductIndexCall() {
        factory(Product::class, 3)->create();

        $this->fakeAuthentication();

        $response = $this->get("/api/products");

        $this->assertCount(3, $response->decodeResponseJson());
        $response->assertStatus(200);
    }

    public function testProductShowCall() {
        $product = factory(Product::class)->create();

        $this->fakeAuthentication();

        $response = $this->get("/api/products/{$product->id}");

        $this->assertEquals($product->id, $response->decodeResponseJson()['id']);
        $response->assertStatus(200);
    }

    public function testProductStoreCall() {
        $name = $this->faker->word;
        $description = $this->faker->text;
        $price = $this->faker->numberBetween(1, 2000);

        $this->fakeAuthentication();

        $response = $this->post("/api/products", [
            'name' => $name,
            'description' => $description,
            'price' => $price
        ]);

        $response->assertStatus(201);

        $storedModel = Product::where('name', $name)
            ->where('description', $description)
            ->where('price', $price)
            ->first();

        $this->assertNotNull($storedModel);
    }

    public function testProductUpdateCall() {
        $product = factory(Product::class)->create();

        $newName = $this->faker->word;
        $newDescription = $this->faker->text;
        $newPrice = $this->faker->numberBetween(1, 2000);

        $this->fakeAuthentication();

        $response = $this->put("/api/products/{$product->id}", [
            'name' => $newName,
            'description' => $newDescription,
            'price' => $newPrice
        ]);

        $response->assertStatus(200);

        $updatedModel = Product::where('name', $newName)
            ->where('description', $newDescription)
            ->where('price', $newPrice)
            ->first();

        $this->assertNotNull($updatedModel);
    }

    public function testProductDeleteCall() {
        $product = factory(Product::class)->create();

        $this->fakeAuthentication();

        $response = $this->delete("/api/products/{$product->id}");
        $response->assertStatus(200);

        $this->assertNull(Product::find($product->id));
    }

    public function testProductImageUploadCall() {
        $product = factory(Product::class)->create();
        $this->fakeAuthentication();

        Storage::fake('local');
        
        $file = UploadedFile::fake()->image('product1.jpg')->size(100);

        $response = $this->patch("/api/products/{$product->id}/upload-image", [
            'product_image' => $file
        ]);

        $response->assertStatus(200);

        $this->assertEquals('products/'.$file->hashName(), Product::find($product->id)->image);
        Storage::assertExists('products/'.$file->hashName());
    }

    public function testAttachesProductToRequestingUser() {
        $product = factory(Product::class)->create();

        $userId = $this->fakeAuthentication();

        $response = $this->patch("/api/products/{$product->id}/attach-to-user");
        $response->assertStatus(200);

        $this->assertEquals($userId, $product->users()->first()->id);
    }

    public function testDetachesProductFromRequestingUser() {
        $product = factory(Product::class)->create();

        $this->fakeAuthentication();

        Auth::user()->products()->attach($product->id);

        $response = $this->patch("/api/products/{$product->id}/detach-from-user");
        $response->assertStatus(200);

        $this->assertNull($product->users()->first());
    }

    public function testShowsUserProduct() {
        $this->fakeAuthentication();

        $thisUsersProducts = factory(Product::class, 10)->create();
        factory(Product::class, 5)->create(); // Some other user's products - not attached.

        Auth::user()->products()->attach($thisUsersProducts->pluck('id')->all());

        $response = $this->get("/api/products/belongs-to-user");
        $response->assertStatus(200);

        $this->assertCount($thisUsersProducts->count(), $response->decodeResponseJson());
    }
}
