<?php

# Copyright (c)  2015 - <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

class StatsdMetricsAdminPlugin extends StudipPlugin implements SystemPlugin
{
    const NAVIGATION_ID = 'statsdmetricsadmin';
    const CHILD_PLUGIN  = 'StatsdMetricsPlugin';

    function __construct()
    {
        parent::__construct();
        $this->setupNavigation();
    }

    private function setupNavigation()
    {
        global $perm;
        if (!$perm->have_perm('root')) {
            return;
        }


        if (Navigation::hasItem('/admin/config')) {
            $url = PluginEngine::getURL(strtolower(__CLASS__) . '/show');
            Navigation::addItem('/admin/config/' . self::NAVIGATION_ID, new Navigation(__CLASS__, $url));
        }
    }

    #######################################################################

    // ***** EN/DISABLE MAGIC *****

    static function onDisable($id)
    {
        self::deactivateChildPlugin();
    }

    #######################################################################

    // ***** CONTROLLER ACTIONS *****


    // show the admin interface
    function show_action()
    {
        $flash = $this->popFlash();

        $this->requireRoot();

        Metrics::increment("plopp");

        Navigation::activateItem('/admin/config/' . self::NAVIGATION_ID);

        $parameters = array(
              'plugin'   => $this
            , 'flash'    => $flash
            , 'settings' => $this->getSettings()
            , 'active'   => $this->isChildPluginActivated()
        );

        $factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        echo $factory->render('show', $parameters, $GLOBALS['template_factory']->open('layouts/base'));
    }


    // update settings
    function settings_action()
    {
        $this->requireRoot();

        if (Request::method() !== 'POST') {
            throw new AccessDeniedException();
        }

        # get settings
        $settings = Request::getArray('settings');

        # validate them
        list($valid, $err) = $this->validateSettings($settings);
        if (!$valid) {
            $this->redirect('show', compact('err'));
            return;
        }

        $this->updateSettings($settings);

        $this->activateChildPlugin();

        $this->redirect('show', array('info' => _('Aktivierung abgeschlossen!')));
    }

    // deactivate metrics plugin
    function deactivate_action()
    {
        $this->requireRoot();

        if (Request::method() !== 'POST') {
            throw new AccessDeniedException();
        }

        self::deactivateChildPlugin();

        $this->redirect('show', array('info' => _('Deaktivierung abgeschlossen!')));
    }

    #######################################################################

    // ***** BUSINESS LOGIC & HELPERS *****

    private function getSettings()
    {
        require_once 'StatsdMetricsSettings.php';
        return StatsdMetricsSettings::get();
    }

    private function updateSettings($settings)
    {
        require_once 'StatsdMetricsSettings.php';
        StatsdMetricsSettings::set($settings);
    }

    private function validateSettings($settings)
    {
        $errors = array();

        # IP adress of statsd host
        if (!filter_var($settings['ip'], FILTER_VALIDATE_IP)) {
            $errors[] = _('IP ist ungültig.');
        }

        # port of statsd host
        if (!filter_var($settings['port'], FILTER_VALIDATE_INT)) {
            $errors[] = _('Port ist ungültig.');
        }

        if (!preg_match('/^[a-zA-Z0-9]{1,10}$/', $settings['prefix'])) {
            $errors[] = _('Prefix ist ungültig.');
        }

        return array(sizeof($errors) === 0, $errors);
    }


    private function isChildPluginActivated()
    {
        $info = PluginManager::getInstance()->getPluginInfo(self::CHILD_PLUGIN);
        return $info && $info['enabled'];
    }

    private function activateChildPlugin()
    {
          $plugin_manager = PluginManager::getInstance();

          # register
          $additional_class = self::CHILD_PLUGIN;
          $pluginpath = 'luniki/' . __CLASS__;
          $pluginid = $this->getPluginId();
          $id = $plugin_manager->registerPlugin($additional_class, $additional_class, $pluginpath, $pluginid);

          # and activate
          $plugin_manager->setPluginEnabled($id, TRUE);
    }


    private static function deactivateChildPlugin()
    {
        $info = PluginManager::getInstance()->getPluginInfo(self::CHILD_PLUGIN);
        PluginManager::getInstance()->unregisterPlugin($info['id']);
    }


    private function requireRoot()
    {
        global $perm;
        if (!$perm->have_perm('root')) {
            throw new AccessDeniedException();
        }
    }


    private function redirect($action, $flash = null)
    {
        if ($flash) {
            $_SESSION['statsd_flash'] = $flash;
        }
        header('Location: ' . PluginEngine::getURL(strtolower(__CLASS__) . '/' . $action));
    }


    private function popFlash()
    {
        $key = strtolower(__CLASS__) . '_flash';
        $flash = @$_SESSION[$key];
        unset($_SESSION[$key]);
        return $flash;
    }
}
