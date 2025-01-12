<?php
/**
 * This class will test some actions.
 *
 * PHP Version 5
 *
 * @category   Pami
 * @package    Test
 * @subpackage Action
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://marcelog.github.com/
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace PAMI\Client\Impl {
    use PHPUnit\Framework\TestCase as BaseTestCase;

    /**
     * This class will test some actions.
     *
     * PHP Version 5
     *
     * @category   Pami
     * @package    Test
     * @subpackage Action
     * @author     Marcelo Gornstein <marcelog@gmail.com>
     * @license    http://marcelog.github.com/ Apache License 2.0
     * @link       http://marcelog.github.com/
     */
    class Test_Actions extends BaseTestCase
    {
/*
        private $_properties = array();

        public function setUp() :void
        {
            global $mockTime;
            $this->_properties = array();
            $mockTime = true;
        }
*/
        private function _start(array $write, \PAMI\Message\Action\ActionMessage $action)
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $writeLogin = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $writeLogin);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
            if ($action instanceof \PAMI\Message\Action\DBGetAction) {
                $event = array(
                'Response: Success',
                'EventList: start',
                'ActionID: 1432.123',
                '',
                'Event: DBGetResponse',
                'ActionID: 1432.123',
                ''
                );
            } elseif ($action instanceof \PAMI\Message\Action\CommandAction) {
                $event = array(
                'Response: Success',
                'ActionID: 1432.123',
                'Message: Command output follows',
                'Output: Changing mystate1 to INUSE',
                ''
                );
            } else {
                $event = array(
                'Response: Success',
                'ActionID: 1432.123',
                ''
                );
            }
            setFgetsMock($event, $write);
            $response = $client->send($action);
            $this->assertTrue($response instanceof \PAMI\Message\Response\Response);
            return array('client' => $client, 'response' => $response);
        }
        /**
         * @test
         */
        public function can_absolute_timeout()
        {
            $write = array(
            "action: AbsoluteTimeout\r\nactionid: 1432.123\r\nchannel: SIP/asd\r\ntimeout: 10\r\n"
            );
            $action = new \PAMI\Message\Action\AbsoluteTimeoutAction('SIP/asd', 10);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_login()
        {
            $write = array(implode("\r\n", array(
            'action: Login',
            'actionid: 1432.123',
            'username: foo',
            'secret: bar',
            ''
            )));
            $action = new \PAMI\Message\Action\LoginAction('foo', 'bar');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_login_with_events()
        {
            $write = array(implode("\r\n", array(
            'action: Login',
            'actionid: 1432.123',
            'username: foo',
            'secret: bar',
            'events: all',
            ''
            )));
            $action = new \PAMI\Message\Action\LoginAction('foo', 'bar', 'all');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_agent_logoff()
        {
            $write = array(implode("\r\n", array(
            'action: AgentLogoff',
            'actionid: 1432.123',
            'agent: asd',
            'soft: true',
            ''
            )));
            $action = new \PAMI\Message\Action\AgentLogoffAction('asd', true);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_agents()
        {
            $write = array(implode("\r\n", array(
            'action: Agents',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\AgentsAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_atxfer()
        {
            $write = array(implode("\r\n", array(
            'action: Atxfer',
            'actionid: 1432.123',
            'channel: Channel',
            'exten: Exten',
            'context: Context',
            ''
            )));
            $action = new \PAMI\Message\Action\AttendedTransferAction('Channel', 'Exten');
            $action->setContext('Context');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_blindTransfer()
        {
            $write = array(implode("\r\n", array(
            'action: BlindTransfer',
            'actionid: 1432.123',
            'channel: channel',
            'exten: exten',
            'context: context',
            ''
            )));
            $action = new \PAMI\Message\Action\BlindTransferAction('channel', 'exten', 'context');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_bridge()
        {
            $write = array(implode("\r\n", array(
            'action: Bridge',
            'actionid: 1432.123',
            'channel1: channel1',
            'channel2: channel2',
            'tone: true',
            ''
            )));
            $action = new \PAMI\Message\Action\BridgeAction('channel1', 'channel2', true);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_bridge_info()
        {
            $bridge_uniqueid = '57cb3a7e-0fa3-4e28-924f-d7728b0d7a9a';

            $write = array(implode("\r\n", array(
            'action: BridgeInfo',
            'actionid: 1432.123',
            'bridgeuniqueid: '. $bridge_uniqueid,
            ''
            )));
            $action = new \PAMI\Message\Action\BridgeInfoAction($bridge_uniqueid);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_bridge_list()
        {
            $write = array(implode("\r\n", array(
            'action: BridgeList',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\BridgeListAction();
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_bridge_list_with_bridge_type()
        {
            $write = array(implode("\r\n", array(
            'action: BridgeList',
            'actionid: 1432.123',
            'bridgetype: basic',
            ''
            )));
            $bridgeType = 'basic';
            $action = new \PAMI\Message\Action\BridgeListAction($bridgeType);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_challenge()
        {
            $write = array(implode("\r\n", array(
            'action: Challenge',
            'actionid: 1432.123',
            'authtype: test',
            ''
            )));
            $action = new \PAMI\Message\Action\ChallengeAction('test');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_change_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: ChangeMonitor',
            'actionid: 1432.123',
            'channel: channel',
            'file: file',
            ''
            )));
            $action = new \PAMI\Message\Action\ChangeMonitorAction('channel', 'file', true);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_command()
        {
            $write = array(implode(
                "\r\n",
                array(
                    'action: Command',
                    'actionid: 1432.123',
                    'command: devstate change Custom:mystate1 INUSE',
                    ''
                )
            ));
            $action = new \PAMI\Message\Action\CommandAction("devstate change Custom:mystate1 INUSE");
            $result = $this->_start($write, $action);
            $response = $result['response'];
            $this->assertTrue($response instanceof \PAMI\Message\Response\CommandResponse);
            $this->assertEquals($response->getCommandOutput(), "Changing mystate1 to INUSE");
        }
        /**
         * @test
         */
        public function can_confbridge_list_rooms()
        {
            $conference = 'conf-59dba3997444e5';
            $write = array(implode("\r\n", array(
            'action: ConfbridgeListRooms',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeListRoomsAction();
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_list()
        {
            $conference = 'conf-59dba3997444e5';
            $write = array(implode("\r\n", array(
            'action: ConfbridgeList',
            'actionid: 1432.123',
            'conference: ' . $conference,
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeListAction($conference);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_mute()
        {
            $write = array(implode("\r\n", array(
            'action: ConfbridgeMute',
            'actionid: 1432.123',
            'channel: channel',
            'conference: conference',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeMuteAction('channel', 'conference');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_unmute()
        {
            $write = array(implode("\r\n", array(
            'action: ConfbridgeUnmute',
            'actionid: 1432.123',
            'channel: channel',
            'conference: conference',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeUnmuteAction('channel', 'conference');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_lock()
        {
            $write = array(implode("\r\n", array(
            'action: ConfbridgeLock',
            'actionid: 1432.123',
            'conference: conference',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeLockAction('conference');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_unlock()
        {
            $write = array(implode("\r\n", array(
            'action: ConfbridgeUnlock',
            'actionid: 1432.123',
            'conference: conference',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeUnlockAction('conference');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_kick()
        {
            $write = array(implode("\r\n", array(
            'action: ConfbridgeKick',
            'actionid: 1432.123',
            'conference: conference',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeKickAction('conference', 'channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_start_record()
        {
            $write = array(implode("\r\n", array(
            'action: ConfbridgeStartRecord',
            'actionid: 1432.123',
            'conference: conference',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeStartRecordAction('conference');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_confbridge_stop_record()
        {
            $write = array(implode("\r\n", array(
            'action: ConfbridgeStopRecord',
            'actionid: 1432.123',
            'conference: conference',
            ''
            )));
            $action = new \PAMI\Message\Action\ConfbridgeStopRecordAction('conference');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_core_settings()
        {
            $write = array(implode("\r\n", array(
            'action: CoreSettings',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\CoreSettingsAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_show_devices()
        {
            $write = array(implode("\r\n", array(
            'action: DongleShowDevices',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleShowDevicesAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_reload()
        {
            $write = array(implode("\r\n", array(
            'action: DongleReload',
            'actionid: 1432.123',
            'when: when',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleReloadAction('when');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_restart()
        {
            $write = array(implode("\r\n", array(
            'action: DongleRestart',
            'actionid: 1432.123',
            'when: when',
            'device: device',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleRestartAction('when', 'device');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_reset()
        {
            $write = array(implode("\r\n", array(
            'action: DongleReset',
            'actionid: 1432.123',
            'device: device',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleResetAction('device');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_send_pdu()
        {
            $write = array(implode("\r\n", array(
            'action: DongleSendPDU',
            'actionid: 1432.123',
            'device: device',
            'pdu: pdu',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleSendPDUAction('device', 'pdu');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_send_ussd()
        {
            $write = array(implode("\r\n", array(
            'action: DongleSendUSSD',
            'actionid: 1432.123',
            'device: device',
            'ussd: ussd',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleSendUSSDAction('device', 'ussd');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_stop()
        {
            $write = array(implode("\r\n", array(
            'action: DongleStop',
            'actionid: 1432.123',
            'when: when',
            'device: device',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleStopAction('when', 'device');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_start()
        {
            $write = array(implode("\r\n", array(
            'action: DongleStart',
            'actionid: 1432.123',
            'device: device',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleStartAction('device');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dongle_sms_send()
        {
            $write = array(implode("\r\n", array(
            'action: DongleSendSMS',
            'actionid: 1432.123',
            'device: device',
            'number: number',
            'message: message',
            ''
            )));
            $action = new \PAMI\Message\Action\DongleSendSMSAction('device', 'number', 'message');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_core_status()
        {
            $write = array(implode("\r\n", array(
            'action: CoreStatus',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\CoreStatusAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_create_config()
        {
            $write = array(implode("\r\n", array(
            'action: CreateConfig',
            'actionid: 1432.123',
            'filename: file.conf',
            ''
            )));
            $action = new \PAMI\Message\Action\CreateConfigAction('file.conf');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dahdi_dndoff()
        {
            $write = array(implode("\r\n", array(
            'action: DAHDIDNDoff',
            'actionid: 1432.123',
            'dahdichannel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\DAHDIDNDOffAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dahdi_dndon()
        {
            $write = array(implode("\r\n", array(
            'action: DAHDIDNDon',
            'actionid: 1432.123',
            'dahdichannel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\DAHDIDNDOnAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dahdi_dialoffhook()
        {
            $write = array(implode("\r\n", array(
            'action: DAHDIDialOffhook',
            'actionid: 1432.123',
            'dahdichannel: channel',
            'number: number',
            ''
            )));
            $action = new \PAMI\Message\Action\DAHDIDialOffHookAction('channel', 'number');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dahdi_hangup()
        {
            $write = array(implode("\r\n", array(
            'action: DAHDIHangup',
            'actionid: 1432.123',
            'dahdichannel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\DAHDIHangupAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dahdi_restart()
        {
            $write = array(implode("\r\n", array(
            'action: DAHDIRestart',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\DAHDIRestartAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dahdi_show_channels()
        {
            $write = array(implode("\r\n", array(
            'action: DAHDIShowChannels',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\DAHDIShowChannelsAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dahdi_transfer()
        {
            $write = array(implode("\r\n", array(
            'action: DAHDITransfer',
            'actionid: 1432.123',
            'dahdichannel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\DAHDITransferAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dbdel()
        {
            $write = array(implode("\r\n", array(
            'action: DBDel',
            'actionid: 1432.123',
            'family: family',
            'key: key',
            ''
            )));
            $action = new \PAMI\Message\Action\DBDelAction('family', 'key');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dbdeltree()
        {
            $write = array(implode("\r\n", array(
            'action: DBDelTree',
            'actionid: 1432.123',
            'family: family',
            'key: key',
            ''
            )));
            $action = new \PAMI\Message\Action\DBDelTreeAction('family', 'key');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dbget()
        {
            $write = array(implode("\r\n", array(
            'action: DBGet',
            'actionid: 1432.123',
            'family: family',
            'key: key',
            ''
            )));
            $action = new \PAMI\Message\Action\DBGetAction('family', 'key');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_dbput()
        {
            $write = array(implode("\r\n", array(
            'action: DBPut',
            'actionid: 1432.123',
            'family: family',
            'key: key',
            'val: val',
            ''
            )));
            $action = new \PAMI\Message\Action\DBPutAction('family', 'key', 'val');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_events_off()
        {
            $write = array(implode("\r\n", array(
            'action: Events',
            'actionid: 1432.123',
            'eventmask: off',
            ''
            )));
            $action = new \PAMI\Message\Action\EventsAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_events()
        {
            $write = array(implode("\r\n", array(
            'action: Events',
            'actionid: 1432.123',
            'eventmask: a,b,c',
            ''
            )));
            $action = new \PAMI\Message\Action\EventsAction(array('a', 'b', 'c'));
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_extension_state()
        {
            $write = array(implode("\r\n", array(
            'action: ExtensionState',
            'actionid: 1432.123',
            'exten: exten',
            'context: context',
            ''
            )));
            $action = new \PAMI\Message\Action\ExtensionStateAction('exten', 'context');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_get_config()
        {
            $write = array(implode("\r\n", array(
            'action: GetConfig',
            'actionid: 1432.123',
            'filename: file.conf',
            'category: category',
            ''
            )));
            $action = new \PAMI\Message\Action\GetConfigAction('file.conf', 'category');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_get_configjson()
        {
            $write = array(implode("\r\n", array(
            'action: GetConfigJSON',
            'actionid: 1432.123',
            'filename: file.conf',
            ''
            )));
            $action = new \PAMI\Message\Action\GetConfigJSONAction('file.conf', 'category');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_get_var()
        {
            $write = array(implode("\r\n", array(
            'action: Getvar',
            'actionid: 1432.123',
            'variable: var',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\GetVarAction('var', 'channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_hangup()
        {
            $write = array(implode("\r\n", array(
            'action: Hangup',
            'actionid: 1432.123',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\HangupAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_hangup_with_cause()
        {
            $write = array(implode("\r\n", array(
            'action: Hangup',
            'actionid: 1432.123',
            'channel: channel',
            'cause: 5',
            ''
            )));
            $action = new \PAMI\Message\Action\HangupAction('channel', 5);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_jabbersend()
        {
            $write = array(implode("\r\n", array(
            'action: JabberSend',
            'actionid: 1432.123',
            'jabber: jabber',
            'jid: jid',
            'screenname: jid',
            'message: message',
            ''
            )));
            $action = new \PAMI\Message\Action\JabberSendAction('jabber', 'jid', 'message');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_list_categories()
        {
            $write = array(implode("\r\n", array(
            'action: ListCategories',
            'actionid: 1432.123',
            'filename: file.conf',
            ''
            )));
            $action = new \PAMI\Message\Action\ListCategoriesAction('file.conf');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_list_commands()
        {
            $write = array(implode("\r\n", array(
            'action: ListCommands',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\ListCommandsAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_local_optimize_away()
        {
            $write = array(implode("\r\n", array(
            'action: LocalOptimizeAway',
            'actionid: 1432.123',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\LocalOptimizeAwayAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_mailbox_count()
        {
            $write = array(implode("\r\n", array(
            'action: MailboxCount',
            'actionid: 1432.123',
            'mailbox: mailbox',
            ''
            )));
            $action = new \PAMI\Message\Action\MailboxCountAction('mailbox');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_mailbox_status()
        {
            $write = array(implode("\r\n", array(
            'action: MailboxStatus',
            'actionid: 1432.123',
            'mailbox: mailbox',
            ''
            )));
            $action = new \PAMI\Message\Action\MailboxStatusAction('mailbox');
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_meetme_list()
        {
            $write = array(implode("\r\n", array(
            'action: MeetmeList',
            'actionid: 1432.123',
            'conference: conference',
            ''
            )));
            $action = new \PAMI\Message\Action\MeetmeListAction('conference');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_meetme_mute()
        {
            $write = array(implode("\r\n", array(
            'action: MeetmeMute',
            'actionid: 1432.123',
            'meetme: meetme',
            'usernum: usernum',
            ''
            )));
            $action = new \PAMI\Message\Action\MeetmeMuteAction('meetme', 'usernum');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_meetme_unmute()
        {
            $write = array(implode("\r\n", array(
            'action: MeetmeUnmute',
            'actionid: 1432.123',
            'meetme: meetme',
            'usernum: usernum',
            ''
            )));
            $action = new \PAMI\Message\Action\MeetmeUnmuteAction('meetme', 'usernum');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_mix_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: MixMonitor',
            'actionid: 1432.123',
            'channel: channel',
            'file: file',
            'options: options',
            ''
            )));
            $action = new \PAMI\Message\Action\MixMonitorAction('channel');
            $action->setFile('file');
            $action->setOptions(array('o', 'p', 't', 'i', 'o', 'n', 's'));
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_mute_mix_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: MixMonitorMute',
            'actionid: 1432.123',
            'channel: channel',
            'state: 1',
            'direction: both',
            ''
            )));
            $action = new \PAMI\Message\Action\MixMonitorMuteAction('channel', false, 'read');
            $action->setState(true);
            $action->setDirection('both');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_module_check()
        {
            $write = array(implode("\r\n", array(
            'action: ModuleCheck',
            'actionid: 1432.123',
            'module: module',
            ''
            )));
            $action = new \PAMI\Message\Action\ModuleCheckAction('module');
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_module_load()
        {
            $write = array(implode("\r\n", array(
            'action: ModuleLoad',
            'actionid: 1432.123',
            'module: module',
            'loadtype: load',
            ''
            )));
            $action = new \PAMI\Message\Action\ModuleLoadAction('module');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_module_reload()
        {
            $write = array(implode("\r\n", array(
            'action: ModuleLoad',
            'actionid: 1432.123',
            'module: module',
            'loadtype: reload',
            ''
            )));
            $action = new \PAMI\Message\Action\ModuleReloadAction('module');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_module_unload()
        {
            $write = array(implode("\r\n", array(
            'action: ModuleLoad',
            'actionid: 1432.123',
            'module: module',
            'loadtype: unload',
            ''
            )));
            $action = new \PAMI\Message\Action\ModuleUnloadAction('module');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: Monitor',
            'actionid: 1432.123',
            'channel: channel',
            'file: file',
            ''
            )));
            $action = new \PAMI\Message\Action\MonitorAction('channel', 'file');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_monitor_filemix()
        {
            $write = array(implode("\r\n", array(
            'action: Monitor',
            'actionid: 1432.123',
            'channel: channel',
            'file: file',
            'format: wav',
            'mix: false',
            ''
            )));
            $action = new \PAMI\Message\Action\MonitorAction('channel', 'file');
            $action->setFormat('wav');
            $action->setMix(false);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_voicemail_users_list()
        {
            $write = array(implode("\r\n", array(
            'action: VoicemailUsersList',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\VoicemailUsersListAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_pause_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: PauseMonitor',
            'actionid: 1432.123',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\PauseMonitorAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_unpause_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: UnpauseMonitor',
            'actionid: 1432.123',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\UnpauseMonitorAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_stop_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: StopMonitor',
            'actionid: 1432.123',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\StopMonitorAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_stop_mix_monitor()
        {
            $write = array(implode("\r\n", array(
            'action: StopMixMonitor',
            'actionid: 1432.123',
            'channel: channel',
            'mixmonitorid: mix_monitor',
            ''
            )));
            $action = new \PAMI\Message\Action\StopMixMonitorAction('channel');
            $action->setMixMonitorId('mix_monitor');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_mix_monitor_mute()
        {
            $write = array(implode("\r\n", array(
            'action: MixMonitorMute',
            'actionid: 1432.123',
            'channel: channel',
            'state: 1',
            'direction: write',
            ''
            )));
            $action = new \PAMI\Message\Action\MixMonitorMuteAction('channel');
            $action->setState(true);
            $action->setDirection('write');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_status()
        {
            $write = array(implode("\r\n", array(
            'action: Status',
            'actionid: 1432.123',
            'channel: channel',
            'variables: a,b,c',
            ''
            )));
            $action = new \PAMI\Message\Action\StatusAction('channel');
            $action->setVariables(['a', 'b', 'c']);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_show_dialplan()
        {
            $write = array(implode("\r\n", array(
            'action: ShowDialPlan',
            'actionid: 1432.123',
            'context: context',
            'extension: extension',
            ''
            )));
            $action = new \PAMI\Message\Action\ShowDialPlanAction('context', 'extension');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_add_extension_to_dialplan()
        {
            $write = array(implode("\r\n", array(
            'action: DialplanExtensionAdd',
            'actionid: 1432.123',
            'context: Context',
            'extension: Extension',
            'priority: Priority',
            'application: Application',
            'replace: true',
            'applicationdata: ApplicationData',
            ''
            )));
            $action = new \PAMI\Message\Action\DialplanExtensionAddAction('Context', 'Extension', 'Priority', 'Application');
            $action->setApplicationData('ApplicationData');
            $action->setReplace(true);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_remove_extension_to_dialplan()
        {
            $write = array(implode("\r\n", array(
            'action: DialplanExtensionRemove',
            'actionid: 1432.123',
            'context: Context',
            'extension: Extension',
            'priority: Priority',
            ''
            )));
            $action = new \PAMI\Message\Action\DialplanExtensionRemoveAction('Context', 'Extension');
            $action->setPriority('Priority');
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_set_var()
        {
            $write = array(implode("\r\n", array(
            'action: Setvar',
            'actionid: 1432.123',
            'variable: variable',
            'value: value',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\SetVarAction('variable', 'value', 'channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_reload()
        {
            $write = array(implode("\r\n", array(
            'action: Reload',
            'actionid: 1432.123',
            'module: module',
            ''
            )));
            $action = new \PAMI\Message\Action\ReloadAction('module');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_ping()
        {
            $write = array(implode("\r\n", array(
            'action: Ping',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\PingAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_send_text()
        {
            $write = array(implode("\r\n", array(
            'action: SendText',
            'actionid: 1432.123',
            'channel: channel',
            'message: message',
            ''
            )));
            $action = new \PAMI\Message\Action\SendTextAction('channel', 'message');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_sip_show_registry()
        {
            $write = array(implode("\r\n", array(
            'action: SIPshowregistry',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\SIPShowRegistryAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_sip_peers()
        {
            $write = array(implode("\r\n", array(
            'action: Sippeers',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\SIPPeersAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_sip_notify()
        {
            $write = array(implode("\r\n", array(
            'action: SIPnotify',
            'actionid: 1432.123',
            'channel: channel',
            ''
            )));
            $action = new \PAMI\Message\Action\SIPNotifyAction('channel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_sip_show_peer()
        {
            $write = array(implode("\r\n", array(
            'action: SIPshowpeer',
            'actionid: 1432.123',
            'peer: peer',
            ''
            )));
            $action = new \PAMI\Message\Action\SIPShowPeerAction('peer');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_sip_qualify_peer()
        {
            $write = array(implode("\r\n", array(
            'action: Sipqualifypeer',
            'actionid: 1432.123',
            'peer: peer',
            ''
            )));
            $action = new \PAMI\Message\Action\SIPQualifyPeerAction('peer');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_vgsm_sms_tx()
        {
            $write = array(implode("\r\n", array(
            'action: vgsm_sms_tx',
            'actionid: 1432.123',
            'account: account',
            'x-sms-concatenate-total-messages: totalmsg',
            'x-sms-concatenate-sequence-number: seqnum',
            'x-sms-concatenate-refid: refid',
            'x-sms-class: class',
            'content: content',
            'x-sms-me: me',
            'content-transfer-encoding: encoding',
            'content-type: type',
            'to: to',
            ''
            )));
            $action = new \PAMI\Message\Action\VGSMSMSTxAction;
            $action->setAccount('account');
            $action->setConcatTotalMsg('totalmsg');
            $action->setConcatSeqNum('seqnum');
            $action->setConcatRefId('refid');
            $action->setSmsClass('class');
            $action->setContent('content');
            $action->setMe('me');
            $action->setContentEncoding('encoding');
            $action->setContentType('type');
            $action->setTo('to');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_parked_calls()
        {
            $write = array(implode("\r\n", array(
            'action: ParkedCalls',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\ParkedCallsAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queues()
        {
            $write = array(implode("\r\n", array(
            'action: Queues',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\QueuesAction;
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_redirect()
        {
            $write = array(implode("\r\n", array(
            'action: Redirect',
            'actionid: 1432.123',
            'channel: channel',
            'exten: extension',
            'context: context',
            'priority: priority',
            'extrapriority: extrapriority',
            'extracontext: extracontext',
            'extraexten: extraextension',
            'extrachannel: extrachannel',
            ''
            )));
            $action = new \PAMI\Message\Action\RedirectAction('channel', 'extension', 'context', 'priority');
            $action->setExtraPriority('extrapriority');
            $action->setExtraContext('extracontext');
            $action->setExtraExtension('extraextension');
            $action->setExtraChannel('extrachannel');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_unpause()
        {
            $write = array(implode("\r\n", array(
            'action: QueuePause',
            'actionid: 1432.123',
            'queue: queue',
            'reason: reason',
            'interface: interface',
            'paused: false',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueUnpauseAction('interface', 'queue', 'reason');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_pause()
        {
            $write = array(implode("\r\n", array(
            'action: QueuePause',
            'actionid: 1432.123',
            'queue: queue',
            'reason: reason',
            'interface: interface',
            'paused: true',
            ''
            )));
            $action = new \PAMI\Message\Action\QueuePauseAction('interface', 'queue', 'reason');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_summary()
        {
            $write = array(implode("\r\n", array(
            'action: QueueSummary',
            'actionid: 1432.123',
            'queue: queue',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueSummaryAction('queue');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_status()
        {
            $write = array(implode("\r\n", array(
            'action: QueueStatus',
            'actionid: 1432.123',
            'queue: queue',
            'member: member',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueStatusAction('queue', 'member');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_reset()
        {
            $write = array(implode("\r\n", array(
            'action: QueueReset',
            'actionid: 1432.123',
            'queue: queue',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueResetAction('queue');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_rule()
        {
            $write = array(implode("\r\n", array(
            'action: QueueRule',
            'actionid: 1432.123',
            'rule: rule',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueRuleAction('rule');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_remove()
        {
            $write = array(implode("\r\n", array(
            'action: QueueRemove',
            'actionid: 1432.123',
            'queue: queue',
            'interface: interface',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueRemoveAction('queue', 'interface');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_reload()
        {
            $write = array(implode("\r\n", array(
            'action: QueueReload',
            'actionid: 1432.123',
            'queue: queue',
            'members: yes',
            'rules: yes',
            'parameters: yes',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueReloadAction('queue', true, true, true);
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_penalty()
        {
            $write = array(implode("\r\n", array(
            'action: QueuePenalty',
            'actionid: 1432.123',
            'interface: interface',
            'penalty: penalty',
            'queue: queue',
            ''
            )));
            $action = new \PAMI\Message\Action\QueuePenaltyAction('interface', 'penalty', 'queue');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_log()
        {
            $write = array(implode("\r\n", array(
            'action: QueueLog',
            'actionid: 1432.123',
            'event: event',
            'queue: queue',
            'message: message',
            'interface: member',
            'uniqueid: uniqueid',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueLogAction('queue', 'event');
            $action->setMessage('message');
            $action->setMemberName('member');
            $action->setUniqueId('uniqueid');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_add()
        {
            $write = array(implode("\r\n", array(
            'action: QueueAdd',
            'actionid: 1432.123',
            'interface: interface',
            'queue: queue',
            'paused: true',
            'membername: member',
            'penalty: penalty',
            'stateinterface: state',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueAddAction('queue', 'interface');
            $action->setPaused('true');
            $action->setMemberName('member');
            $action->setPenalty('penalty');
            $action->setStateInterface('state');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_queue_member_ringinuse()
        {
            $write = array(implode("\r\n", array(
            'action: QueueMemberRingInUse',
            'actionid: 1432.123',
            'interface: interface',
            'ringinuse: true',
            'queue: queue',
            ''
            )));
            $action = new \PAMI\Message\Action\QueueMemberRingInUse('interface', false);
            $action->setRingInUse(true);
            $action->setQueue('queue');
        
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_play_dtmf()
        {
            $write = array(implode("\r\n", array(
            'action: PlayDTMF',
            'actionid: 1432.123',
            'channel: channel',
            'digit: 1',
            ''
            )));
            $action = new \PAMI\Message\Action\PlayDTMFAction('channel', '1');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_park()
        {
            $write = array(implode("\r\n", array(
            'action: Park',
            'actionid: 1432.123',
            'channel: channel1',
            'channel2: channel2',
            'timeout: timeout',
            'parkinglot: lot',
            ''
            )));
            $action = new \PAMI\Message\Action\ParkAction('channel1', 'channel2', 'timeout', 'lot');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_agi()
        {
            $write = array(implode("\r\n", array(
            'action: AGI',
            'actionid: 1432.123',
            'channel: channel1',
            'command: an agi command',
            'commandid: blah',
            ''
            )));
            $action = new \PAMI\Message\Action\AGIAction('channel1', 'an agi command', 'blah');
            $result = $this->_start($write, $action);
        }
        /**
         * @test
         */
        public function can_originate()
        {
            $write = array(implode("\r\n", array(
            'action: Originate',
            'actionid: 1432.123',
            'channel: channel',
            'codecs: a,b',
            'async: true',
            'account: account',
            'callerid: clid',
            'timeout: timeout',
            'data: data',
            'application: app',
            'priority: priority',
            'context: context',
            'exten: extension',
            'Variable: a=b',
            ''
            )));
            $action = new \PAMI\Message\Action\OriginateAction('channel');
            $action->setCodecs(array('a', 'b'));
            $action->setAsync(true);
            $action->setAccount('account');
            $action->setCallerId('clid');
            $action->setTimeout('timeout');
            $action->setData('data');
            $action->setApplication('app');
            $action->setPriority('priority');
            $action->setContext('context');
            $action->setExtension('extension');
            $action->setVariable('a', 'b');
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_logoff()
        {
            $write = array(implode("\r\n", array(
            'action: Logoff',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\LogoffAction();
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_user_event()
        {
            $write = array(implode("\r\n", array(
            'action: UserEvent',
            'actionid: 1432.123',
            'userevent: FooEvent',
            'foo: Bar',
            'bar: Foo',
            ''
            )));
            $action = new \PAMI\Message\Action\UserEventAction('FooEvent', ['Foo' => 'Bar', 'Bar' => 'Foo']);
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_wait_event()
        {
            $write = array(implode("\r\n", array(
            'action: WaitEvent',
            'actionid: 1432.123',
            'timeout: 20',
            ''
            )));
            $action = new \PAMI\Message\Action\WaitEventAction(20);
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_set_actionid()
        {
            $action = new \PAMI\Message\Action\PingAction();
            // ActionID is between 0 and 69 characters long.
            $actionID = '121234567890123456789012345678901234567890';
            $action->setActionID($actionID);
            $this->assertSame($actionID, $action->getActionID());
        }

        /**
         * @test
         * expectedException \PAMI\Exception\PAMIException
         */
        public function cannot_set_actionid_longer_than_69_characters()
        {
            $this->expectException(\PAMI\Exception\PAMIException::class);
            $action = new \PAMI\Message\Action\PingAction();
            // A 70-character long ActionID
            $action->setActionID('1234567890123456789012345678901234567890123456789012345678901234567890');
        }

        /**
         * @test
         * expectedException \PAMI\Exception\PAMIException
         */
        public function cannot_set_empty_actionid()
        {
            $this->expectException(\PAMI\Exception\PAMIException::class);
            $action = new \PAMI\Message\Action\PingAction();
            // An empty ActionID
            $action->setActionID('');
        }

        /**
         * @test
         */
        public function can_update_config()
        {
            $number = 9876;
            $writeCreate = array( implode("\r\n", array(
            'action: UpdateConfig',
            'actionid: 1432.123',
            'srcfilename: sip.conf',
            'dstfilename: sip.conf',
            'action-000000: NewCat',
            'cat-000000: '.$number,
            'action-000001: Append',
            'cat-000001: '.$number,
            'var-000001: username',
            'value-000001: test',
            'action-000002: Append',
            'cat-000002: '.$number,
            'var-000002: secret',
            'value-000002: secret',
            'action-000003: Append',
            'cat-000003: '.$number,
            'match-000003: some_match',
            'line-000003: line',
            'action-000004: Append',
            'cat-000004: '.$number,
            'options-000004: option',
            ''
            )) );

            $actionCreate = new \PAMI\Message\Action\UpdateConfigAction();

            $actionCreate->setSrcFilename('sip.conf');
            $actionCreate->setDstFilename('sip.conf');

            $actionCreate->setAction('NewCat');
            $actionCreate->setCat($number);

            $actionCreate->setAction('Append');
            $actionCreate->setCat($number);
            $actionCreate->setVar('username');
            $actionCreate->setValue('test');

            $actionCreate->setAction('Append');
            $actionCreate->setCat($number);
            $actionCreate->setVar('secret');
            $actionCreate->setValue('secret');

            $actionCreate->setAction('Append');
            $actionCreate->setCat($number);
            $actionCreate->setMatch('some_match');
            $actionCreate->setLine('line');
        
            $actionCreate->setAction('Append');
            $actionCreate->setCat($number);
            $actionCreate->setOptions('option');
        
            $result = $this->_start($writeCreate, $actionCreate);

            $writeDelete = array( implode("\r\n", array(
            'action: UpdateConfig',
            'actionid: 1432.123',
            'srcfilename: sip.conf',
            'dstfilename: sip.conf',
            'reload: yes',
            'action-000000: DelCat',
            'cat-000000: '.$number,
            ''
            )) );

            $actionDelete = new \PAMI\Message\Action\UpdateConfigAction();

            $actionDelete->setSrcFilename('sip.conf');
            $actionDelete->setDstFilename('sip.conf');
            $actionDelete->setReload(true);
            $actionDelete->setAction('DelCat');
            $actionDelete->setCat($number);

            $result = $this->_start($writeDelete, $actionDelete);
        }

        /**
         * @test
         */
        public function can_pjsip_register()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPRegister',
            'actionid: 1432.123',
            'registration: Register',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPRegisterAction('Register');
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_qualify()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPQualify',
            'actionid: 1432.123',
            'endpoint: Endpoint',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPQualifyAction('Endpoint');
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_show_endpoint()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPShowEndpoint',
            'actionid: 1432.123',
            'endpoint: Endpoint',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPShowEndpointAction('Endpoint');
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_show_endpoints()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPShowEndpoints',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPShowEndpointsAction();
            $result = $this->_start($write, $action);
        }
        
        /**
         * @test
         */
        public function can_pjsip_show_respourcelists()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPShowResourceLists',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPShowResourceListsAction();
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_show_registrations_inbound_action()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPShowRegistrationsInbound',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPShowRegistrationsInboundAction();
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_show_registrations_outbound_action()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPShowRegistrationsOutbound',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPShowRegistrationsOutboundAction();
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_show_subscriptions_inbound_action()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPShowSubscriptionsInbound',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPShowSubscriptionsInboundAction();
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_show_subscriptions_outbound_action()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPShowSubscriptionsOutbound',
            'actionid: 1432.123',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPShowSubscriptionsOutboundAction();
            $result = $this->_start($write, $action);
        }

        /**
         * @test
         */
        public function can_pjsip_unregister_action()
        {
            $write = array(implode("\r\n", array(
            'action: PJSIPUnregister',
            'actionid: 1432.123',
            'registration: Registration',
            ''
            )));
            $action = new \PAMI\Message\Action\PJSIPUnregisterAction('Registration');
            $result = $this->_start($write, $action);
        }
    }
}
