
require 'db/Pg.pl';

$stmt{'foltialib.changefilestatus.1'} = "UPDATE foltia_subtitle SET filestatus = ?, lastupdate = datetime('now', 'localtime') WHERE pid = ?";

1;
