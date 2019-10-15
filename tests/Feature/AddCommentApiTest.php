<?php

namespace Tests\Feature;

use App\Photo;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;


class AddCommentApiTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDataBase;

    public function setUp():void
    {
      parent::setUp();

      $this->user = factory(User::class)->create();

    }

    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function should_コメントを追加できる()
  {
      factory(Photo::class)->create();
      $photo = Photo::first();

      $content = 'sample content';

      $response = $this->actingAs($this->user)
          ->json('POST', route('photo.comment', [
              'photo' => $photo->id,
          ]), compact('content'));

      $comments = $photo->comments()->get();

      $response->assertStatus(201)
          // JSONフォーマットが期待通りであること
          ->assertJsonFragment([
              "author" => [
                  "name" => $this->user->name,
              ],
              "content" => $content,
          ]);

      // DBにコメントが1件登録されていること
      $this->assertEquals(1, $comments->count());
      // 内容がAPIでリクエストしたものであること
      $this->assertEquals($content, $comments[0]->content);
  }
}
