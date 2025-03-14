<?php
namespace App\Services;
use Barryvanveen\Lastfm\Lastfm;
use GuzzleHttp\Client;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class LastFmManager {

  /**
   * The Barryvanveen\Lastfm\Lastfm instance.
   *
   * @var Barryvanveen\Lastfm\Lastfm $lastfm
   */
  private $lastfm;

  /**
   * The LastFm User.
   *
   * @var string $lastFmUser
   */
  private $lastFmUser;

  /**
   * The constructor.
   */
  public function __construct(
    string $lastFmApiKey,
    string $lastFmUser,
    protected TagAwareCacheInterface $cache,
    protected RequestStack $requestStack,
  ) {
    if (!$lastFmApiKey || empty($lastFmApiKey)) {
      throw new \Exception('LAST_FM_API_KEY is not set in the .env file.');
    }
    if (!$lastFmUser || empty($lastFmUser)) {
      throw new \Exception('LAST_FM_USER is not set in the .env file.');
    }
    $this->lastfm = new Lastfm(new Client(), $lastFmApiKey);
    $this->lastFmUser = $lastFmUser;
    $this->cache = $cache;
  }

  /**
   * Gets the Lastfm user info.
   */
  public function getUserInfo($force = false) {
    $lastfm = $app_cache->get('lastfm__account', function (ItemInterface $item): ?array {
      $app->getLogger()->debug('LastFM account cache miss: refreshing from LastFm API');
      $expiresAt = (new \DateTime())->setTimeZone(new \DateTimeZone('America/New_York'))->setTimestamp(strtotime('+1 day'));
      $item->tag(['lastfm', 'api']);

      try {
        $lastfm['account'] = $lastfmApi->userInfo($lastfmUsername)->get();
      }
      catch (Barryvanveen\Lastfm\Exceptions\ResponseException $exception) {
        $item->expiresAt((new \DateTime())->setTimeZone(new \DateTimeZone('America/New_York'))->setTimestamp(strtotime('now')));
      }
      catch (\Exception $e) {
        $item->expiresAt((new \DateTime())->setTimeZone(new \DateTimeZone('America/New_York'))->setTimestamp(strtotime('now')));
      }
      return $lastfm;
    }, ($force ? INF : 1.0));
  }

  /**
   * Gets the latest Lastfm scrobble.
   */
  public function getLatestScrobble($force = false) {
    $api = $this->lastfm;
    $cache = $this->cache;
    $lastplayed = $cache->get('lastfm__last_played_track', function (ItemInterface $item) use ($api): ?array {
      $expiresAt = (new \DateTime())->setTimeZone(new \DateTimeZone('America/New_York'))->setTimestamp(strtotime('+1 minute'));
      //$app->getLogger()->debug('LastFM tracks cache miss: refreshing from LastFm API');
      $item->expiresAt($expiresAt);
      $item->tag(['lastfm', 'api']);
      try {
        $lastplayed = $api->userRecentTracks('cupcakezealot')->limit(1)->get();
        if (!empty($lastplayed)) {
          $lastplayed = current($lastplayed);
          if (isset($lastplayed['date']['uts'])) {
            $lastplayed['timestamp'] = (new \DateTime())->setTimeZone(new \DateTimeZone('GMT'))->setTimestamp($lastplayed['date']['uts'])->setTimeZone(new \DateTimeZone('Europe/London'));
            $lastplayed['relative'] = $this->time_elapsed_string($lastplayed['timestamp']);
          }
          else {
            $lastplayed['relative'] = 'right now';
          }
        }
      }
      catch (Barryvanveen\Lastfm\Exceptions\ResponseException $exception) {
        //$app->getLogger()->error($exception->getMessage());
        dump($e->getMessage());
        $item->expiresAt((new \DateTime())->setTimeZone(new \DateTimeZone('America/New_York'))->setTimestamp(strtotime('now')));
      }
      catch (\Exception $e) {
        dump($e->getMessage());
        $item->expiresAt((new \DateTime())->setTimeZone(new \DateTimeZone('America/New_York'))->setTimestamp(strtotime('+1 minute')));
      }
      return $lastplayed ?? [];
    }, ($force ? INF : 1.0));
    return $lastplayed;
  }

  /**
   * Returns a relative date string
   *
   * @param [type] $datetime
   * @param boolean $full
   * @return string
   * @see https://stackoverflow.com/a/18602474
   */
  public function time_elapsed_string($datetime, $full = false): ?string {
    $now = (new \DateTime)->setTimeZone(new \DateTimeZone('Europe/London'));
    if ($datetime instanceof \DateTime) {
      $ago = $datetime;
    }
    else {
      $ago = new \DateTime($datetime);
    }
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
  }
}
