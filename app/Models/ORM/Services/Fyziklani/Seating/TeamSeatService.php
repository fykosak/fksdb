<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani\Seating;

use Fykosak\NetteORM\Service;

final class TeamSeatService extends Service
{
}
/*
 * INSERT INTO fyziklani_seat (fyziklani_room_id, layout_x, layout_y)
 * select ftp.room_id as fyziklani_room_id, ftp.x_coordinate as layout_x, ftp.y_coordinate as layout_y
 * from fyziklani_team_position ftp where x_coordinate is not null
 *
 * INSERT INTO fyziklani_seat (fyziklani_room_id, layout_x, layout_y)
 * select ftp.room_id as fyziklani_room_id, ftp.row as layout_x, ftp.col as layout_y
 * from fyziklani_team_position ftp where ftp.`row` is not null
 *
 insert into fyziklani_team_seat (e_fyziklani_team_id, fyziklani_seat_id)
select ftp.e_fyziklani_team_id,
       (
           select fs.fyziklani_seat_id
           from fyziklani_seat fs
           where (
               # (abs(ftp.row = fs.layout_x) < 0.01 and abs(ftp.col = fs.layout_y) < 0.01)

               (ftp.x_coordinate = fs.layout_x and ftp.y_coordinate = fs.layout_y)
               )
             AND (fs.fyziklani_room_id = IF(ftp.e_fyziklani_team_id > 10, 10, ftp.e_fyziklani_team_id))
       ) as 'fyziklani_seat_id'
from fyziklani_team_position ftp where ftp.e_fyziklani_team_id > 3000
*/
