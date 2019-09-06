<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;

class RelayCallingCallPlayTest extends RelayCallingBaseActionCase
{
  protected static $notificationFinished;
  public static $success;
  public static $fail;

  public static function setUpBeforeClass() {
    self::$notificationFinished = json_decode('{"event_type":"calling.call.play","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","state":"finished"}}');
    self::$success = json_decode('{"result":{"code":"200","message":"message","control_id":"'.self::UUID.'"}}');
    self::$fail = json_decode('{"result":{"code":"400","message":"some error","control_id":"'.self::UUID.'"}}');
  }

  protected function setUp() {
    parent::setUp();

    $this->_setCallReady();
  }

  public function testPlaySuccess(): void {
    $msg = $this->_playMsg([
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->play(
      ['type' => 'audio', 'url' => 'audio.mp3'], // flattened
      ['type' => 'tts', 'text' => 'Welcome', 'gender' => 'male'], // flattened
      ['type' => 'silence', 'params' => ['duration' => 5]] // type/params
    )->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPlayFail(): void {
    $msg = $this->_playMsg([
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ]);
    $this->_mockFailResponse($msg, self::$fail);

    $this->call->play(
      ['type' => 'audio', 'url' => 'audio.mp3'], // flattened
      ['type' => 'tts', 'text' => 'Welcome', 'gender' => 'male'], // flattened
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResult', $result);
      $this->assertFalse($result->isSuccessful());
    });
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPlayAsyncSuccess(): void {
    $msg = $this->_playMsg([
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->playAsync(
      ['type' => 'audio', 'url' => 'audio.mp3'], // Flattened
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done([$this, '__asyncPlayCheck']);
  }

  public function testPlayAsyncFail(): void {
    $msg = $this->_playMsg([
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ]);
    $this->_mockFailResponse($msg, self::$fail);

    $this->call->playAsync(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function ($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\PlayAction', $action);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPlayAudio(): void {
    $msg = $this->_playMsg([
      ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->playAudio('url-to-audio.mp3')->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPlayAudioAsync(): void {
    $msg = $this->_playMsg([
      ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->playAudioAsync('url-to-audio.mp3')->done([$this, '__asyncPlayCheck']);
  }

  public function testPlaySilence(): void {
    $msg = $this->_playMsg([
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->playSilence(5)->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPlaySilenceAsync(): void {
    $msg = $this->_playMsg([
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->playSilenceAsync(5)->done([$this, '__asyncPlayCheck']);
  }

  public function testPlayTTS(): void {
    $msg = $this->_playMsg([
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->playTTS(['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPlayTTSAsync(): void {
    $msg = $this->_playMsg([
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']]
    ]);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->playTTSAsync(['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__asyncPlayCheck']);
  }

  /**
   * Callable to not repeat the same function for every SYNC play test
   */
  public function __syncPlayCheck($result) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResult', $result);
    $this->assertTrue($result->isSuccessful());
    $this->assertObjectHasAttribute('state', $result->getEvent()->payload);
  }

  /**
   * Callable to not repeat the same function for every ASYNC play test
   */
  public function __asyncPlayCheck($action) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\PlayAction', $action);
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResult', $action->getResult());
    $this->assertFalse($action->isCompleted());

    $this->calling->notificationHandler(self::$notificationFinished);

    $this->assertTrue($action->isCompleted());
  }

  private function _playMsg(Array $playList) {
    return $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'play' => $playList
      ]
    ]);
  }
}