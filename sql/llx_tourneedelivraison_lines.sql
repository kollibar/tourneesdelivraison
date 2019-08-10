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


CREATE TABLE llx_tourneedelivraison_lines(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	note_public text,
	note_private text,
	date_creation datetime NOT NULL,
	tms timestamp NOT NULL,
	fk_user_creat integer NOT NULL,
	fk_user_modif integer,
	import_key varchar(14),
	BL integer NOT NULL,
	facture integer NOT NULL,
	etiquettes integer NOT NULL,
	rang integer NOT NULL,
	fk_tournee integer NOT NULL,
	fk_tournee_incluse integer,
	fk_soc integer,
	fk_adresselivraison integer,
	type integer NOT NULL,
	tpstheorique integer,
	infolivraison text,
	fk_parent_line integer,
	force_email_soc integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
