<?php if (!defined('W3TC')) die(); ?>
<tr>
    <th><label for="cdn_transparentcdn_companyid"><?php _e('Company id:', 'w3-total-cache'); ?></th>
    <td>
        <input id="cdn_transparentcdn_companyid" class="w3tc-ignore-change" type="text"
           <?php $this->sealing_disabled('cdn') ?> name="cdn.transparentcdn.companyid" value="<?php echo esc_attr($this->_config->get_string('cdn.transparentcdn.companyid')); ?>" size="60" />
    </td>
</tr><tr>
    <th style="width: 300px;"><label for="cdn_transparentcdn_apikey"><?php _e('Api key:', 'w3-total-cache'); ?></label></th>
    <td>
        <input id="cdn_transparentcdn_apikey" class="w3tc-ignore-change" type="text"
           <?php $this->sealing_disabled('cdn') ?> name="cdn.transparentcdn.apikey" value="<?php echo esc_attr($this->_config->get_string('cdn.transparentcdn.apikey')); ?>" size="60" />
    </td>
</tr>
<tr>
    <th><label for="cdn_transparentcdn_apisecret"><?php _e('API secret:', 'w3-total-cache'); ?></th>
    <td>
        <input id="cdn_transparentcdn_apisecret" class="w3tc-ignore-change" type="password"
           <?php $this->sealing_disabled('cdn') ?> name="cdn.transparentcdn.apisecret" value="<?php echo esc_attr($this->_config->get_string('cdn.transparentcdn.apisecret')); ?>" size="60" />
    </td>
</tr>
<tr>
    <th><?php _e('Replace site\'s hostname with:', 'w3-total-cache'); ?></th>
    <td>
		<?php $cnames = $this->_config->get_array('cdn.transparentcdn.domain'); include W3TC_INC_DIR . '/options/cdn/common/cnames.php'; ?>
        <br /><span class="description"><?php _e('Enter the hostname provided by your <acronym>CDN</acronym> provider, this value will replace your site\'s hostname in the <acronym title="Hypertext Markup Language">HTML</acronym>.', 'w3-total-cache'); ?></span>
    </td>
</tr>
<tr>
	<th colspan="2">
        <input id="cdn_test" class="button {type: 'transparentcdn', nonce: '<?php echo wp_create_nonce('w3tc'); ?>'}" type="button" value="<?php _e('Test transparentcdn', 'w3-total-cache'); ?>" /> <span id="cdn_test_status" class="w3tc-status w3tc-process"></span>
    </th>
</tr>
