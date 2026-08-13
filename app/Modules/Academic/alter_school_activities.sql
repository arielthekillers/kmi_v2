ALTER TABLE school_activities 
ADD COLUMN academic_calendar_id INT NULL AFTER academic_year_id;

ALTER TABLE school_activities
ADD CONSTRAINT fk_school_activities_calendar
FOREIGN KEY (academic_calendar_id) REFERENCES academic_calendar_events(id)
ON DELETE CASCADE;
