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


CREATE TABLE llx_tourneeunique(
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	ref varchar(128) NOT NULL,
	label varchar(255),
	description text,
	note_public text,
	note_private text,
	date_creation datetime NOT NULL,
	tms timestamp NOT NULL,
	fk_user_creat integer NOT NULL,
	fk_user_modif integer,
	import_key varchar(14),
	statut integer NOT NULL,
	km integer,
	dureeTrajet integer,
	fk_tourneedelivraison integer,
	date_tournee date,
	date_prochaine date,
	ae_datelivraisonidentique integer,
	ae_1ere_future_cmde integer,
	ae_1elt_par_cmde integer,
	change_date_affectation integer,
	model_pdf varchar(255)
) ENGINE=innodb;
