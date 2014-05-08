Succinct Data Work Flow
=======================

Creating or updating forms
--------------------------

ODK forms are created in the normal way.  Not all features are
supported by succinct data at this time.

STATUS: This functionality is operational.

Preparing ODK forms for use with Succinct Data
----------------------------------------------

Forms are prepared by running xml2recipe over them, which will produce
the recipe file and template file.

The recipe file describes the type of each field in the form to be
included in the succinct data.  

BUG: The recipe file may need hand modification for a variety of
reasons. The recipe file can be hand modified before first distribution to
phones, for example to exclude certain fields or place limits on the
lengths of fields.  However, if you modify the recipe file then any
succinct data messages created to the previous version of the recipe
will be impossible to read, and succinct data will grossly
misinterpret any such messages.  Eventually we intend to support the
use of succinct data attributes in the ODK XML form so that hand
modification is never required.

The recipe and template files should be copied to the succinct directory
of your succinct data bundle marshalling area. You will also need to
include a smac.dat file for SMAC to reference when compressing
succinct data messages.  

NOTE: The same version of smac.dat must be used for
all forms, or else messages will be grossly misinterpretted by
succinct data resulting in gobbledygook.  Just use the version we have
supplied to avoid problems.

STATUS: This functionality is operational.

Pushing form bundles to phones
------------------------------

Use the assemble_bundle script in the examples directory of this
repository to create a .succinct.config directory that can be
installed on phones.  

The .succinct.config file should be copied into
/servalproject/sam/configs on the SD card of the phone.

You then need to start "Serval SAM" on the phone and go into
preferences and eventually select the file (or it will be selected
automatically) and reload configuration.

For some partly out of date instructions from the previous life of
Serval SAM that may nonetheless be useful:

http://developer.servalproject.org/dokuwiki/doku.php?id=content:servalsam:main_page

STATUS: This functionality is operational.

BUG: Reloading configuration deletes all previous form instances, when
it probably shouldn't.

Using forms
-----------

The succinctdata branch of
http://github.com/servalproject/survey-acquisition-management ("Serval
SAM") is used to select the form to complete.  It automatically
launches ODK Collect, and then captures the form when complete, and
extracts it's succinct data for transmission.  

There is (currently) about a 20 second delay from form completion
before the succinct data is extracted.  We will look into reducing or
removing this delay.

The succinct data is written to the sdcard in the
servalproject/sam/succinct_spool directory.

A separate program is expected to collect the succinct data, and
transmit it via appropriate means.  See below.

STATUS: This functionality is operational.

BUG: At present the succinct data does not identify the form from
which it came, or the version of that form.  We intend to remedy this
in the very near future so that multiple forms and versions of forms
can be used without confusion.

Transmitting completed forms via SMS/inReach
--------------------------------------------

We intend to write program that sends succinct data messages by either
SMS or inReach depending on which is available.

Transmission will be able to be automatic or manually controlled.

STATUS: This program has yet to be written.

Receiving completed forms via SMS/inReach
-----------------------------------------

On the receive side, the succinct data messages must be written to a
file, and then fed through the SMAC program which takes the following
arguments: 

smac recipe decompress <recipename> <succinct data file> <output file>

To obtain the XML form, then a further call is required, and requires
the matching XML template file:

smac recipe rexml <output file from previous command> <template file>
<output XML file>

BUG: At present, the name of the succinct data recipe must be supplied to
enable decompression and regeneration of the XML form. This will
change in the very near future, when succinct data messages will
encode the identity of the recipe internally, and will automatically
select the correct recipe file from a directory of recipes.  It is
likely that a single command will perform both of the above steps,
recreating the XML, and placing it in an appropriate directory for any
further processing.

STATUS: Work in progress.  Many of the components exist, but are not
yet integrated.