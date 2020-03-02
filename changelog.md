# Changelog
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
