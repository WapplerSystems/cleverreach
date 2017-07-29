.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _installation:

Installation
============

.. important::

The extension needs to be installed as any other extension of TYPO3 CMS:

#. Switch to the module “Extension Manager”.

#. Get the extension

   #. **Get it from the Extension Manager:** Press the “Retrieve/Update”
      button and search for the extension key *cleverreach* and import the
      extension from the repository.

   #. **Get it from typo3.org:** You can always get current version from
      `http://typo3.org/extensions/repository/view/cleverreach/current/
      <http://typo3.org/extensions/repository/view/cleverreach/current/>`_ by
      downloading either the t3x or zip version. Upload
      the file afterwards in the Extension Manager.

   #. **Use composer**: Use `composer require svewap/cleverreach`.


Latest version from git
-----------------------
You can get the latest version from git by using the git command:

.. code-block:: bash

   git clone https://github.com/svewap/cleverreach.git

Preparation: Include static TypoScript
--------------------------------------

The extension ships some TypoScript code which needs to be included.

#. Switch to the root page of your site.

#. Switch to the **Template module** and select *Info/Modify*.

#. Press the link **Edit the whole template record** and switch to the tab *Includes*.

#. Select **CleverReach (cleverreach)** at the field *Include static (from extensions):*

|img-plugin-ts|

Constants
^^^^^^^^^

After adding the typoscript you can enter the cleverreach API settings. This API uses the REST format.

As a admin you can enter default settings for Form and Group IDs. This can maybe overridden by editor.

|img-plugin-ts-contants|
