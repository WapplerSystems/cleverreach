.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _howToStart:

Powermail: How to start
=======================
This walkthrough will help you to implement the extension cleverreach for powermail at your
TYPO3 site. The installation is covered :ref:`here <installation>`.




Add a powermail plugin to a page
----------------------

Add a powermail plugin with form as usual.


General settings for powermail
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

On the plugin page you have to choose the mode if you want to enable the finisher.

|img-powermail-mode|


Opt in form
^^^^^^^^^^^

You can add the special validator to you email field, but you don't have to.

|img-powermail-validator-optin|

Opt out form
^^^^^^^^^^^^

You can add the special validator to you email field, but you don't have to.

|img-powermail-validator-optout|


Contact form with optional subscription
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

For example, if you want to let your customers choose to sign up for the newsletter, you can add a checkbox.
It is very important that the variable name is **newslettercondition** and the value of the checkbox is **1**.


|img-powermail-checkbox1|

|img-powermail-checkbox2|

