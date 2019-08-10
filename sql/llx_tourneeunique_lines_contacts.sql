-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.


CREATE TABLE llx_tourneeunique_lines_contacts(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	fk_soc integer,
	note_public text,
	note_private text,
	date_creation datetime NOT NULL,
	tms timestamp NOT NULL,
	fk_user_creat integer NOT NULL,
	fk_user_modif integer,
	import_key varchar(14),
	fk_socpeople integer,
	rang integer NOT NULL,
	fk_tournee_lines integer NOT NULL,
	fk_parent_line integer,
	no_email integer,
	sms integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
