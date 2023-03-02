<?php

namespace App\Console\Commands\Crawl;

use App\Services\CrawlerService;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;
use Spatie\Browsershot\Browsershot;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Laracasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:laracasts {menu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「laracasts」';

    /**
     * Execute the console command.
     */
    public function handle(CrawlerService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::onceUsingId(1);

        $serverUrl = 'http://localhost:4444';

        $desiredCapabilities = DesiredCapabilities::chrome();

        // Disable accepting SSL certificates
        // $desiredCapabilities->setCapability('acceptSslCerts', false);

        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['--headless']);
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $driver = RemoteWebDriver::create($serverUrl, $desiredCapabilities);
        $driver->get('https://laracasts.com/series');

        $driver->wait()->until(\Facebook\WebDriver\WebDriverExpectedCondition::titleIs('Laracasts Series'));

        $element = $driver->findElement(
            WebDriverBy::cssSelector('body')
        );

        $title = $element->getDomProperty('innerHTML');

        $driver->quit();

        $crawler = Crawler::new($title);

        $header = $crawler->parse([
            'title' => ['header h3','text'],
        ]);

        dd($header);


        $posts = $service->handleLaracasts($this->argument('menu'));


        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
