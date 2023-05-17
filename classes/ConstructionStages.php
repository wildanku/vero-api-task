<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data)
	{
		// validate form input 
		$validated = $data->validate([
			'name'			=> 'required|max:255',
			'startDate' 	=> 'required|dateTimeTz',
			'endDate'		=> 'dateTimeTz|after:startDate',
			'durationUnit'	=> 'in:HOURS,DAYS,WEEKS',
			'color'			=> 'hex',
			'externalId'	=> 'max:255',
			'status'		=> 'in:NEW,PLANNED,DELETED'
		]);

		if(isset($validated['errors'])) {
			return $validated;
		}
		
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
				(name, start_date, end_date, duration, durationUnit, color, externalId, status)
				VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

	/**
	 * Updating resource by ID
	 * @param int $id
	 * @return Array
	 */
	public function update(ConstructionStagesUpdate $data, int $id)
	{
		$validated = $data->validate([
			'name'			=> 'required|max:255',
			'startDate' 	=> 'required|dateTimeTz',
			'endDate'		=> 'dateTimeTz|after:startDate',
			'durationUnit'	=> 'in:HOURS,DAYS,WEEKS',
			'color'			=> 'hex',
			'externalId'	=> 'max:255',
			'status'		=> 'in:NEW,PLANNED,DELETED'
		]);

		if(isset($validated['errors'])) {
			return $validated;
		}

		$stmt = $this->db->prepare("
			UPDATE construction_stages
			SET 
				name = :name, 
				start_date = :start_date, 
				end_date = :end_date, 
				duration = :duration, 
				durationUnit = :durationUnit, 
				color = :color, 
				externalId = :externalId, 
				status = :status
			WHERE id = :id
		");
		
		$stmt->execute([
			'id' => $id,
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $this->calculateDuration($data->startDate, $data->endDate, $data->durationUnit), // call a function to calculate duration automaticaly 
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);

		return $this->getSingle($id);
	}	

	/** 
	 * Deleting resource by ID
	 * @param int $id
	 * @return boolean
	*/
	public function delete(int $id)
	{
		try {
			$stmt = $this->db->prepare("DELETE FROM construction_stages WHERE id = :id");
			$stmt->execute(['id' => $id]);
		} catch(Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Calculate duration based on startDate and endDate
	 * @param string $startDate
	 * @param string $endDate 
	 * @param string $durationUnit in HOURS,DAYS,WEEKS
	 * @return float
	 */
	public function calculateDuration(string $startDate, string $endDate, string $durationUnit)
	{
		$duration = 0;

		$startAt = new DateTime($startDate);
		$endAt = new DateTime($endDate);

		$interval = $startAt->diff($endAt);

		if(isset($durationUnit) && $durationUnit === "HOURS") {
			$duration = ($interval->format('%a')*24) + $interval->format('%h');
		}

		else if(isset($durationUnit) && $durationUnit === "DAYS") {
			$duration = $interval->format('%a');
		} 

		else if(isset($durationUnit) && $durationUnit === "WEEKS") {
			$duration = intval($interval->format('%a') / 7);
		} 

		else {
			$duration = $interval->format('%a');
		}

		return $duration;
	}
}