
require 'db/Pg.pl';

$stmt{'foltialib.changefilestatus.1'} = "UPDATE foltia_subtitle SET filestatus = ?, lastupdate = datetime('now', 'localtime') WHERE pid = ?";

$stmt{'xmltv2foltia.commitdb.2'} ="INSERT INTO foltia_epg VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)";


1;
