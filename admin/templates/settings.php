<?php


if (!defined('ABSPATH')) {
    exit;
}

?>
<form action="" method="post">
    <table class="form-table">
        <tbody>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_test_mode">Test Mode</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_TEST_MODE; ?> id="discord_test_mode" type="checkbox" <?php echo get_option($DISCORD_TEST_MODE) === "on" ? "checked" : null; ?> />
                    <p class="description">Enable test mode</p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_oauth_url">OAuth URL</label>
                </th>
                <td>
                    <textarea name=<?php echo $DISCORD_OAUTH_URL ?> id="discord_oauth_url"><?php echo get_option($DISCORD_OAUTH_URL); ?></textarea>
                    <p class="description">Discord OAuth2 URL <a href="<?php echo get_option($DISCORD_OAUTH_URL); ?>">Test</a></p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_client_key">Client Key</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_CLIENT_KEY; ?> id="discord_client_key" type="text" value="<?php echo get_option($DISCORD_CLIENT_KEY); ?>" />
                    <p class="description">Discord Client Key</p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_client_secret">Client Secret</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_CLIENT_SECRET; ?> id="discord_client_secret" type="text" value="<?php echo get_option($DISCORD_CLIENT_SECRET); ?>" />
                    <p class="description">Discord Client Secret</p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_bot_token">Bot Token</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_BOT_TOKEN; ?> id="discord_bot_token" type="text" value="<?php echo get_option($DISCORD_BOT_TOKEN); ?>" />
                    <p class="description">Discord Bot Token</p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_guild_id">Guild ID</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_GUILD_ID; ?> id="discord_guild_id" type="text" value="<?php echo get_option($DISCORD_GUILD_ID); ?>" />
                    <p class="description">Discord Guild (server) ID</p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_test_guild_id">Test Guild ID</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_TEST_GUILD_ID; ?> id="discord_test_guild_id" type="text" value="<?php echo get_option($DISCORD_TEST_GUILD_ID); ?>" />
                    <p class="description">Discord Guild (server) ID for Testing</p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_joiner_role_id">New Joiner Role ID</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_JOINER_ROLE_ID; ?> id="discord_joiner_role_id" type="text" value="<?php echo get_option($DISCORD_JOINER_ROLE_ID); ?>" />
                    <p class="description">Role IDs for new joiner to the guild</p>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row" valign="top">
                    <label for="discord_test_joiner_role_id">Test New Joiner Role ID</label>
                </th>
                <td>
                    <input name=<?php echo $DISCORD_TEST_JOINER_ROLE_ID; ?> id="discord_test_joiner_role_id" type="text" value="<?php echo get_option($DISCORD_TEST_JOINER_ROLE_ID); ?>" />
                    <p class="description">Role IDs for new joiner to the guild</p>
                </td>
            </tr>
        </tbody>
    </table>
    <p class="submit">
        <input type="submit" name="save_attribute" id="submit" class="button-primary" value="Save">
    </p>
</form>