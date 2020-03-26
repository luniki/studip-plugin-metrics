<h2>Statsd-Einstellungen</h2>


<? if (isset($flash['info'])) : ?>
    <?= MessageBox::info($flash['info']) ?>
<? endif ?>

<? if (isset($flash['err'])) : ?>
          <?= join("", array_map(function ($err) { return MessageBox::error($err); }, $flash['err'])) ?>
<? endif ?>

<? if ($active) : ?>

    <dl>
        <dt>Statsd-IP</dt>   <dd><?= htmlReady($settings['ip']) ?></dd>
        <dt>Statsd-Port</dt> <dd><?= htmlReady($settings['port']) ?></dd>
        <dt>Präfix</dt>      <dd><?= htmlReady($settings['prefix']) ?></dd>
    </dl>

    <form action="<?= PluginEngine::getLink($plugin, array(), 'deactivate') ?>" method="post">
        <?= \Studip\Button::createAccept(_("Deaktivieren")) ?>
    </form>

<? else : ?>

    <form action="<?= PluginEngine::getLink($plugin, array(), 'settings') ?>" method="post">

        <fieldset>

            <legend>Wo befindet sich Ihr Statsd?</legend>

            <label>
                <?= _('IP:') ?>
                <input required type="text" name="settings[ip]" value="<?= htmlReady($settings['ip']) ?>">
            </label>

            <label>
                <?= _('Port:') ?>
                <input required type="text" name="settings[port]" value="<?= htmlReady($settings['port']) ?>">
            </label>

        </fieldset>

        <fieldset>

            <legend>Unter welchem Präfix wollen Sie die Daten dieses Stud.IPs speichern?</legend>

            <label>
                <?= _('Präfix:') ?>
                <input required type="text" maxlength="10" name="settings[prefix]" value="<?= htmlReady($settings['prefix']) ?>">
            </label>
        </fieldset>

        <div class="button-group">
            <?= \Studip\Button::createAccept(_("Übernehmen und aktivieren")) ?>
        </div>
    </form>

<? endif ?>
