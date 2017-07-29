.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _installation:

Installation for Powermail
==========================


Additional to the basic typoscript record, you have to include the powermail typoscripts after the powermail typoscripts:

|img-plugin-ts-powermail1|


Recommended settings
^^^^^^^^^^^^^^^^^^^^

I recommend to deactivate the spamshield and the client validation, because it's untested.
::

	plugin.tx_powermail.settings.spamshield.enable = 0
	plugin.tx_powermail.settings.validation.client = 0

