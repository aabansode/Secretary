-- Secretary 1.3 (2015-10-02)

ALTER TABLE `#__secretary_folders` ADD `access` INT(11) UNSIGNED NOT NULL DEFAULT '1' AFTER `created_time`;
ALTER TABLE `#__secretary_messages` ADD `created_by_alias` VARCHAR(255) NULL DEFAULT NULL AFTER `created_by`;
ALTER TABLE `#__secretary_messages` CHANGE `created_by` `created_by` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'; 
ALTER TABLE `#__secretary_messages` ADD `fields` TEXT NULL DEFAULT NULL AFTER `template`;
ALTER TABLE `#__secretary_messages` CHANGE `message_id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

INSERT INTO `#__secretary_templates` (`id`, `asset_id`, `business`, `extension`, `state`, `catid`, `title`, `text`, `css`, `fields`, `checked_out`, `checked_out_time`, `language`) VALUES
(NULL, 0, 0, 'messages', 19, 0, 'Kontakt Formular', '<h2>Kontakt</h2>\r\n\r\n<div class="contact">\r\n  <table>\r\n    <tr>\r\n    	<td>{contact-category-title}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td>{contact-firstname} {contact-lastname}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td>{contact-street}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td>{contact-zip} {contact-location}</td>\r\n    </tr>\r\n  </table>\r\n</div>\r\n\r\n<hr />\r\n\r\n{form-start}\r\n<div class="form">\r\n  <table>\r\n    <tr>\r\n    	<td>{form-standard-name-label title=Ihr Name}</td>\r\n    	<td>{form-standard-name}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td>{form-standard-email-label title=Email}</td>\r\n    	<td>{form-standard-email}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td>Ihre Telefonnummer</td>\r\n    	<td>{form-field-phone}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td>{form-standard-subject-label title=Betreff}</td>\r\n    	<td>{form-standard-subject}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td valign="top">{form-standard-text-label title=Nachricht}</td>\r\n    	<td>{form-standard-text}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td>Kopie an mich</td>\r\n    	<td>{form-standard-copy}</td>\r\n    </tr>\r\n    <tr>\r\n    	<td></td>\r\n    	<td>{form-standard-send}</td>\r\n    </tr>\r\n  </table>\r\n</div>\r\n{form-end}\r\n', '', '[[3,"Phone","","text"]]', 0, '0000-00-00 00:00:00', 'de-DE');