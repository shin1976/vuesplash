<?php

namespace Tests\Feature;

use App\Photo;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoSubmitApiTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    public function setUp():void
    {
      parent::setUp();

      $this->user = factory(User::class)->create();
    }
    public function should_ファイルをアップロードできる()
    {
      Storage::fake('s3');
      $response=$this->actingAs($this->user)
      ->json('POST',route('photo.create'),[
        'photo' => UploadedFile::fake()->image('photo.jpg')
      ]);
      $response->assertStatus(201);
      $photo = Photo::first();

      $this->assertRegExp('/^[0-9a-zA-Z-_]{12}$/', $photo->id);

      Storage::cloud()->assertExists($photo->filename);
    }

    public function should_データベースエラーの場合はファイルを保存しない()
    {
      Schema::drop('photos');
      Storage::fake('s3');

      $response = $this->actingAs($this->user)
           ->json('POST', route('photo.create'), [
               'photo' => UploadedFile::fake()->image('photo.jpg'),
           ]);

       // レスポンスが500(INTERNAL SERVER ERROR)であること
       $response->assertStatus(500);

       // ストレージにファイルが保存されていないこと
       $this->assertEquals(0, count(Storage::cloud()->files()));
    }

    public function should_ファイル保存エラーの場合はDBへの挿入はしない()
    {
        // ストレージをモックして保存時にエラーを起こさせる
        Storage::shouldReceive('cloud')
            ->once()
            ->andReturnNull();

        $response = $this->actingAs($this->user)
            ->json('POST', route('photo.create'), [
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        // レスポンスが500(INTERNAL SERVER ERROR)であること
        $response->assertStatus(500);

        // データベースに何も挿入されていないこと
        $this->assertEmpty(Photo::all());
    }
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
