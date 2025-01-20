
# ReadProgress
**ReadProgress** is a WordPress plugin that enhances the reading experience by adding two useful tools to your site, speciallly for long articles.

1. **Reading Progress Bar**  
   A progress bar sticks to the top of the window, showing how much of the text remains to be scrolled until the end of the article.  

2. **Estimated Reading Time** (optional)  
   Displays an estimated reading time for the article, based on a configurable Words Per Minute (WPM) rate set in the admin panel.


## Features
- **Reading Progress Bar**
  - Automatically displays a progress bar at the top of the viewport.
  - Fully customizable: Adjust height and color via the admin panel.

- **Estimated Reading Time**
  - Adds a reading time estimation above the main text content.
  - Configurable WPM rate to suit your audience.

- **Admin Panel Options**
  - Define which post types will use the reading tools.
  - Set a custom selector to identify the element containing the main text.
  - Enable or disable the Estimated Reading Time display.
  - Adjust the height and color of the progress bar.


## Requirements
- WordPress 5.0 or higher.
- PHP 7.4 or higher.


## Installation
1. Upload the `read-progress` folder to the `wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Configure the plugin options under **Settings > Read Progress**:
   - Navigate to the settings page at `/wp-admin/options-general.php?page=rp-options`.


## Usage
1. Go to **Settings > Read Progress** in your WordPress admin dashboard or directly to `/wp-admin/options-general.php?page=rp-options`.
2. Configure the following options:
   - Select post types to apply the tools.
   - Set the selector for the main text container (e.g., `.post-content`).
   - Adjust the height and color of the progress bar.
   - Enable or disable the Estimated Reading Time.
   - Define the WPM rate for calculating the reading time.

3. The tools will automatically appear on your site for the selected post types.

## License
This plugin is licensed under the GNU General Public License 3.0. See the `LICENSE` file for details.


