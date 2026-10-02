<?php
namespace Tests\Feature;
use App\Models\{Portfolio, Service};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
class PortfolioGalleryTest extends TestCase
{
    use DatabaseMigrations;
    public function test_gallery_groups_stages_and_skips_empty_stages(): void
    {
        $service = Service::create(['name'=>'Cat','slug'=>'cat','description'=>'Cat panel','features'=>[]]);
        $portfolio = Portfolio::create(['service_id'=>$service->id,'title'=>'Avanza','slug'=>'avanza','vehicle_type'=>'CAR','is_published'=>true]);
        $portfolio->images()->create(['image_url'=>'https://example.test/after.jpg','cloudinary_public_id'=>'after','stage'=>'AFTER','sort_order'=>0]);
        $portfolio->images()->create(['image_url'=>'https://example.test/before.jpg','cloudinary_public_id'=>'before','stage'=>'BEFORE','sort_order'=>5]);
        $this->get('/portfolio/avanza')->assertOk()
            ->assertSeeInOrder(['Sebelum pengerjaan','before.jpg','Hasil akhir','after.jpg'])
            ->assertDontSee('Proses pengerjaan')
            ->assertDontSee('Belum ada foto pada tahap ini')
            ->assertDontSee('id="stage-PROCESS"',false)
            ->assertSee('loading="lazy"',false);
        $portfolio->update(['is_published'=>false]);
        $this->get('/portfolio/avanza')->assertNotFound();
    }

    public function test_gallery_renders_every_stage_that_has_media(): void
    {
        $service = Service::create(['name'=>'Cat','slug'=>'cat2','description'=>'Cat panel','features'=>[]]);
        $portfolio = Portfolio::create(['service_id'=>$service->id,'title'=>'Colt','slug'=>'colt','vehicle_type'=>'CAR','is_published'=>true]);
        $portfolio->images()->create(['image_url'=>'https://example.test/p.jpg','cloudinary_public_id'=>'p','stage'=>'PROCESS','sort_order'=>1]);
        $portfolio->images()->create(['image_url'=>'https://example.test/v.mp4','cloudinary_public_id'=>'v','stage'=>'PROCESS','media_type'=>'video','sort_order'=>2]);
        $portfolio->images()->create(['image_url'=>'https://example.test/a.jpg','cloudinary_public_id'=>'a','stage'=>'AFTER','sort_order'=>3]);

        $this->get('/portfolio/colt')->assertOk()
            ->assertSeeInOrder(['Proses pengerjaan','p.jpg','v.mp4','Hasil akhir','a.jpg'])
            ->assertDontSee('Sebelum pengerjaan')
            ->assertSee('<video',false);
    }
}
