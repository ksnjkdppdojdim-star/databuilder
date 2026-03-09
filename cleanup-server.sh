#!/bin/bash
sudo rm -rf /var/www/imscp/gui/plugins/DataBuilderIMSCPBetaPlugin
sudo rm -rf /var/www/imscp/gui/themes/databuilder
sudo rm -rf /var/www/imscp/gui/public/databuilder
sudo rm -rf /var/www/imscp/gui/data/cache/databuilder-templates
sudo mysql -u root imscp -e "DELETE FROM plugin WHERE plugin_name='DataBuilderIMSCPPlugin' OR plugin_name='DataBuilderIMSCPBetaPlugin';"
