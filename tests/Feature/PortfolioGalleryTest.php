<?php
namespace Tests\Feature;
use App\Models\{Portfolio, Service};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
class PortfolioGalleryTest extends TestCase
{
    use DatabaseMigrations;
    public function test_gallery_groups_stages_and_handles_empty_stages(): void
    {
        $service = Service::create(['name'=>'Cat','slug'=>'cat','description'=>'Cat panel','features'=>[]]);
        $portfolio = Portfolio::create(['service_id'=>$service->id,'title'=>'Avanza','slug'=>'avanza','vehicle_type'=>'CAR','is_published'=>true]);
        $portfolio->images()->create(['image_url'=>'https://example.test/after.jpg','cloudinary_public_id'=>'after','stage'=>'AFTER','sort_order'=>0]);
        $portfolio->images()->create(['image_url'=>'https://example.test/before.jpg','cloudinary_public_id'=>'before','stage'=>'BEFORE','sort_order'=>5]);
        $this->get('/portfolio/avanza')->assertOk()
            ->assertSeeInOrder(['Sebelum pengerjaan','before.jpg','Proses pengerjaan','Belum ada foto pada tahap ini.','Hasil akhir','after.jpg'])
            ->assertSee('loading="lazy"',false);
        $portfolio->update(['is_published'=>false]);
        $this->get('/portfolio/avanza')->assertNotFound();
    }
}
