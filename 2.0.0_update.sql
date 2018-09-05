# Auth
 ALTER TABLE coral_auth_prod.`Session` MODIFY `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP;
 
# Licensing
ALTER TABLE coral_licensing_prod.`Expression` MODIFY `lastUpdateDate` timestamp NOT NULL default CURRENT_TIMESTAMP;
ALTER TABLE coral_licensing_prod.`ExpressionNote` MODIFY `lastUpdateDate` timestamp NOT NULL default CURRENT_TIMESTAMP;

# Management
ALTER TABLE coral_management_prod.`Expression` MODIFY `lastUpdateDate` timestamp NOT NULL default CURRENT_TIMESTAMP;
ALTER TABLE coral_management_prod.`ExpressionNote` MODIFY `lastUpdateDate` timestamp NOT NULL default CURRENT_TIMESTAMP;

# Resources
ALTER TABLE  coral_resources_prod.`ResourceNote` MODIFY `updateDate` timestamp NOT NULL default CURRENT_TIMESTAMP;
ALTER TABLE  coral_resources_prod.`ResourceStep` ADD  `archivingDate` DATETIME NULL AFTER  `stepEndDate` ;
ALTER TABLE  coral_resources_prod.`ResourceStep` ADD `mailReminderDelay` INT UNSIGNED NULL;
ALTER TABLE  coral_resources_prod.`ResourceStep` ADD  `note` TEXT NULL ;
INSERT INTO coral_resources_prod.`ResourceType` (`resourceTypeID`, `shortName`, `includeStats`) VALUES (NULL, 'Any', NULL);
INSERT INTO coral_resources_prod.`ResourceFormat` (`resourceFormatID`, `shortName`) VALUES (NULL, 'Any');
INSERT INTO coral_resources_prod.`AcquisitionType` (`acquisitionTypeID`, `shortName`) VALUES (NULL, 'Any');
