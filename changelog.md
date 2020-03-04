# Changelog
### 2020-03-03
#### enable GUIs for events
  * new method of eventPresenter isEnabledForEvent($event):bool
    * enable GUIs for single events
  * Payments, Applications (single+team), applications, fyziklani,...
  * Remove Application progress presenter
    * progress charts are moved to list view of application presenter
  * Change source of application time progress charts
----
### 2020-02-29
#### Email_messages
  * add email_message detail GUI
  * add email_message fields:
    * recipient
    * carbon_copy
    * blind_carbon_copy
    * text
  * change email_message resourceId
  * update ACL for email_message
  * spamPresenter is using EntityTrait
    * TODO remove duplicite methods

#### others
  * add new component: Value printer
    * unlike old one, load factory in render method
  * add new FKSDB\NotImplementedException
    * prepare for new error layout 501 
