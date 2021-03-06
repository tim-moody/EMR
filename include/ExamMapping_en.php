<?php

class ExamMapping_en {

	private $EXAM_TYPE_LABORATORY = 0;
	private $EXAM_TYPE_PHYSICAL = 1;

	private $normal_abnormal_array = [
		"Normal",
		"Abnormal"
	];

	private $no_yes_array = [
		"No (Normal)",
		"Yes"
	];

	private $pregnant_options_array = [
		"Not Pregnant (Normal)",
		"Yes Pregnant"
	];

	private $skin_location_array = [
		"Normal",
		"Face",
		"Neck",
		"Shoulders",
		"Chest",
		"Abdomen",
		"Back (Upper)",
		"Back (Lower)",
		"Arms",
		"Hands",
		"Legs",
		"Feet"
	];

	private $skin_lesions_array = [
		"None (Normal)",
		"Raised",
		"Pustules",
		"Vesicles"
	];

	private $lungs_options_array = [
		"Good Air Movement (Normal)",
		"Wheezes",
		"Consolidation",
		"Rhonchi/Rales"
	];

	private $abdomen_options_array = [
		"No (Normal)",
		"Yes",
		"Murphys"
	];

	private $musculoskeletal_options_array = [
		"Normal",
		"Pain",
		"Range of Motion"
	];

	private $musculoskeletal_range_of_motion_array = [
		"Intact (Normal)",
		"Not Intact"
	];

	private $musculoskeletal_upper_extremities_array = [
		"Normal",
		"Shoulders",
		"Elbows",
		"Wrists",
		"Fingers"
	];

	private $musculoskeletal_lower_extremities_array = [
		"Normal",
		"Hips",
		"Knees",
		"Ankles",
		"Feet"
	];

	private $neurologic_options_array = [
		"Normal",
		"RUE",
		"LUE",
		"RLE",
		"LLE"
	];


	private $exam_type_array = [
	    "Laboratory",
	    "Physical"
	];

	//LABORATORY EXAM STUFF
	private $laboratory_exam_array = [
		"Blood",
		"Feces",
		"Urine",
		"Pregnancy"
	];

	private $laboratory_exam_map1;
	private $laboratory_exam_map2;

	private $blood_array = [
		"Normal",
		"HbA1c",
		"Blood Glucose"
	];

	private $blood_option_map1;
	private $blood_option1_array;
	private $blood_option2_array;

	private $feces_array;

	private $urine_array;

	private $pregnancy_array;

	//PHYSICAL EXAM STUFF
	private $physical_exam_array = [
		"General Appearance",
		"Skin",
		"Head",
		"Eyes",
		"Ears",
		"Nose/Sinuses",
		"Mouth/Pharynx",
		"Neck",
		"Chest/Breasts",
		"Lungs",
		"Cardiac",
		"Vascular",
		"Abdomen",
		"Musculoskeletal",
		"Neurologic",
		"Psychiatric",
		"Genitalia/Rectum"
	];

	private $physical_exam_map1;
	private $physical_exam_map2;
	private $physical_exam_map3;
	private $physical_exam_map4;

	//PHySICAL EXAM: GENERAL APPEARANCE
	private $general_appearance_array = [
		"Normal",
		"Ill",
		"In Pain",
		"Lethargic"
	];

	private $general_appearance_option_map1;
	private $general_appearance_option1_array;
	private $general_appearance_option2_array;
	private $general_appearance_option3_array;

	//PHYSICAL EXAM: SKIN
	private $skin_array = [
		"Normal",
		"Skin Color",
		"Lesions"
	];

	private $skin_option_map1;
	private $skin_option_map2;
	private $skin_option1_map1;
	private $skin_option2_map1;

	private $skin_option1_array;
	private $skin_option1_option1_array;
	private $skin_option1_option2_array;
	private $skin_option1_option3_array;
	private $skin_option1_option4_array;
	private $skin_option1_option5_array;
	private $skin_option1_option6_array;
	private $skin_option1_option7_array;
	private $skin_option1_option8_array;
	private $skin_option1_option9_array;
	private $skin_option1_option10_array;
	private $skin_option1_option11_array;

	private $skin_option2_array;
	private $skin_option2_option1_array;
	private $skin_option2_option2_array;
	private $skin_option2_option3_array;
	private $skin_option2_option4_array;
	private $skin_option2_option5_array;
	private $skin_option2_option6_array;
	private $skin_option2_option7_array;
	private $skin_option2_option8_array;
	private $skin_option2_option9_array;
	private $skin_option2_option10_array;
	private $skin_option2_option11_array;

	//PHYSICAL EXAM: HEAD
	private $head_array = [
		"Normal",
		"Lesions to Scalp",
		"Fontanelles"
	];

	private $head_option_map1;
	private $head_option1_array;
	private $head_option2_array = [
		"Normal",
		"Sunken"
	];

	//PHYSICAL EXAM: EYES
	private $eyes_array = [
		"Normal",
		"Acuity",
		"Eyelids",
		"Sclera",
		"Conjuctivae",
		"Pupils",
		"Extraocular Movements",
		"Drainage"
	];

	private $eyes_option_map1;
	private $eyes_option1_array;
	private $eyes_option2_array = [
		"Normal",
		"Red",
		"Pustule"
	];

	private $eyes_option3_array = [
		"Normal",
		"Red",
		"Lesion"
	];

	private $eyes_option4_array = [
		"Normal",
		"Injected"
	];

	private $eyes_option5_array = [
		"Normal",
		"Sluggish",
		"Asymmetric"
	];

	private $eyes_option6_array = [
		"Intact (Normal)",
		"Not Intact"
	];

	private $eyes_option7_array;

	//PHYSICAL EXAM: EARS
	private $ears_array = [
		"Normal",
		"Outer Ear",
		"Canal",
		"Tympanic Membrane",
		"Light Reflex"
	];

	private $ears_option_map1;

	private $ears_option1_array = [
		"Normal",
		"Red",
		"Lesion",
		"Painful Movement"
	];

	private $ears_option2_array = [
		"Normal",
		"Red",
		"Inflamed",
		"Cerumen"
	];

	private $ears_option3_array = [
		"Normal",
		"Red",
		"Inflamed",
		"Fluid"
	];

	private $ears_option4_array;

	//PHYSICAL EXAM: NOSE/SINUSES
	private $nose_sinuses_array = [
		"Normal",
		"Rhonorrhea",
		"Sinus Tenderness"
	];

	private $nose_sinuses_option_map1;
	private $nose_sinuses_option1_array;
	private $nose_sinuses_option2_array;

	//PHYSICAL EXAM: MOUTH/PHARYNX
	private $mouth_pharynx_array = [
		"Normal",
		"Buccal/Oral Mucosa",
		"Condition of Teeth/Gums",
		"Pharynx/Tonsils"
	];

	private $mouth_pharynx_option_map1;
	private $mouth_pharynx_option1_array = [
		"Normal",
		"Moist",
		"Dry",
		"Lesions"
	];

	private $mouth_pharynx_option2_array = [
		"Normal",
		"Carries/Poor",
		"Inflamed"
	];

	private $mouth_pharynx_option3_array = [
		"Normal",
		"Erythematous",
		"Tonsillar Exudates",
		"Pharyngeal Erythema"
	];

	//PHYSICAL EXAM: NECK
	private $neck_array = [
		"Normal",
		"Range of Motion",
		"Throat Pain to Palpation",
		"Lymph Nodes"
	];

	private $neck_option_map1;

	private $neck_option1_array = [
		"Normal",
		"Impaired"
	];

	private $neck_option2_array;

	private $neck_option3_array = [
		"Palpable (Normal)",
		"Not Palpable"
	];
	
	//PHYSICAL EXAM: CHEST
	private $chest_breasts_array = [
		"Normal",
		"Tenderness",
		"Lesions"
	];

	private $chest_breasts_option_map1;
	private $chest_breasts_option1_array;

	private $chest_breasts_option2_array = [
		"None (Normal)",
		"Yes"
	];

	//PHYSICAL EXAM: LUNGS
	private $lungs_array = [
		"Normal",
		"Right",
		"Left"
	];

	private $lungs_option_map1;
	private $lungs_option1_array;
	private $lungs_option2_array;


	//PHYSICAL EXAM: CARDIAC
	private $cardiac_array = [
		"Regular (Normal)",
		"Irregular",
		"Murmurs"	
	];

	//PHYSICAL EXAM: VASCULAR
	private $vascular_array = [
		"Normal",
		"Pulses",
		"Carotid",
		"Femoral",
		"Radial",
		"Presence of Edema"
	];

	private $vascular_option_map1;
	private $vascular_option1_array;
	private $vascular_option2_array;
	private $vascular_option3_array;
	private $vascular_option4_array;
	private $vascular_option5_array = [
		"None (Normal)",
		"Bilateral",
		"Unilateral Right",
		"Unilateral Left"
	];


	//PHYSICAL EXAM: ABDOMEN
	private $abdomen_array = [
		"Normal",
		"Hepatomegaly",
		"Splenomegaly",
		"LUQ Pain",
		"LLQ Pain",
		"RUQ Pain",
		"RLQ Pain",
		"Guarding",
		"Rebound"
	];

	private $abdomen_option_map1;
	private $abdomen_option1_array;
	private $abdomen_option2_array;
	private $abdomen_option3_array;
	private $abdomen_option4_array;
	private $abdomen_option5_array;
	private $abdomen_option6_array;
	private $abdomen_option7_array;
	private $abdomen_option8_array;

	//PHYSICAL EXAM: MUSCULOSKELETAL
	private $musculoskeletal_array = [
		"Normal",
		"Neck",
		"Spine",
		"Upper Extremities Right",
		"Upper Extremities Left",
		"Lower Extremities Right",
		"Lower Extremities Left"
	];

	private $musculoskeletal_option_map1;

	private $musculoskeletal_option_map2;
	private $musculoskeletal_option1_map1;
	private $musculoskeletal_option2_map1;
	private $musculoskeletal_option3_map1;
	private $musculoskeletal_option4_map1;
	private $musculoskeletal_option5_map1;
	private $musculoskeletal_option6_map1;

	private $musculoskeletal_option_map3;

	private $musculoskeletal_option1_map2;
	private $musculoskeletal_option1_option1_map1;
	private $musculoskeletal_option1_option2_map1;

	private $musculoskeletal_option2_map2;
	private $musculoskeletal_option2_option1_map1;
	private $musculoskeletal_option2_option2_map1;

	private $musculoskeletal_option3_map2;
	private $musculoskeletal_option3_option1_map1;
	private $musculoskeletal_option3_option2_map1;
	private $musculoskeletal_option3_option3_map1;
	private $musculoskeletal_option3_option4_map1;

	private $musculoskeletal_option4_map2;
	private $musculoskeletal_option4_option1_map1;
	private $musculoskeletal_option4_option2_map1;
	private $musculoskeletal_option4_option3_map1;
	private $musculoskeletal_option4_option4_map1;

	private $musculoskeletal_option5_map2;
	private $musculoskeletal_option5_option1_map1;
	private $musculoskeletal_option5_option2_map1;
	private $musculoskeletal_option5_option3_map1;
	private $musculoskeletal_option5_option4_map1;

	private $musculoskeletal_option6_map2;
	private $musculoskeletal_option6_option1_map1;
	private $musculoskeletal_option6_option2_map1;
	private $musculoskeletal_option6_option3_map1;
	private $musculoskeletal_option6_option4_map1;


	private $musculoskeletal_option1_array;

	private $musculoskeletal_option1_option1_array;

	private $musculoskeletal_option1_option2_array;


	private $musculoskeletal_option2_array;

	private $musculoskeletal_option2_option1_array;

	private $musculoskeletal_option2_option2_array;


	private $musculoskeletal_option3_array;

	private $musculoskeletal_option3_option1_array;

	private $musculoskeletal_option3_option1_option1_array;

	private $musculoskeletal_option3_option1_option2_array;

	private $musculoskeletal_option3_option2_array;

	private $musculoskeletal_option3_option2_option1_array;

	private $musculoskeletal_option3_option2_option2_array;

	private $musculoskeletal_option3_option3_array;

	private $musculoskeletal_option3_option3_option1_array;

	private $musculoskeletal_option3_option3_option2_array;

	private $musculoskeletal_option3_option4_array;

	private $musculoskeletal_option3_option4_option1_array;

	private $musculoskeletal_option3_option4_option2_array;


	private $musculoskeletal_option4_array;

	private $musculoskeletal_option4_option1_array;

	private $musculoskeletal_option4_option1_option1_array;

	private $musculoskeletal_option4_option1_option2_array;

	private $musculoskeletal_option4_option2_array;

	private $musculoskeletal_option4_option2_option1_array;

	private $musculoskeletal_option4_option2_option2_array;

	private $musculoskeletal_option4_option3_array;

	private $musculoskeletal_option4_option3_option1_array;

	private $musculoskeletal_option4_option3_option2_array;

	private $musculoskeletal_option4_option4_array;

	private $musculoskeletal_option4_option4_option1_array;

	private $musculoskeletal_option4_option4_option2_array;


	private $musculoskeletal_option5_array;

	private $musculoskeletal_option5_option1_array;

	private $musculoskeletal_option5_option1_option1_array;

	private $musculoskeletal_option5_option1_option2_array;

	private $musculoskeletal_option5_option2_array;

	private $musculoskeletal_option5_option2_option1_array;

	private $musculoskeletal_option5_option2_option2_array;

	private $musculoskeletal_option5_option3_array;

	private $musculoskeletal_option5_option3_option1_array;

	private $musculoskeletal_option5_option3_option2_array;

	private $musculoskeletal_option5_option4_array;

	private $musculoskeletal_option5_option4_option1_array;

	private $musculoskeletal_option5_option4_option2_array;


	private $musculoskeletal_option6_array;

	private $musculoskeletal_option6_option1_array;

	private $musculoskeletal_option6_option1_option1_array;

	private $musculoskeletal_option6_option1_option2_array;

	private $musculoskeletal_option6_option2_array;

	private $musculoskeletal_option6_option2_option1_array;

	private $musculoskeletal_option6_option2_option2_array;

	private $musculoskeletal_option6_option3_array;

	private $musculoskeletal_option6_option3_option1_array;

	private $musculoskeletal_option6_option3_option2_array;

	private $musculoskeletal_option6_option4_array;

	private $musculoskeletal_option6_option4_option1_array;

	private $musculoskeletal_option6_option4_option2_array;


	//PHYSICAL EXAM: NEUROLOGIC
	private $neurologic_array = [
		"Normal",
		"Cranial Nerves",
		"Motor Strength",
		"Sensation",
		"Gait"
	];

	private $neurologic_option_map1;

	private $neurologic_option_map2;
	private $neurologic_option2_map1;
	private $neurologic_option3_map1;

	private $neurologic_option1_array;

	private $neurologic_option2_array;
	private $neurologic_option2_option1_array;
	private $neurologic_option2_option2_array;
	private $neurologic_option2_option3_array;
	private $neurologic_option2_option4_array;

	private $neurologic_option3_array;
	private $neurologic_option3_option1_array;
	private $neurologic_option3_option2_array;
	private $neurologic_option3_option3_array;
	private $neurologic_option3_option4_array;

	private $neurologic_option4_array;

	//PHYSICAL EXAM: PSYCHIATRIC
	private $psychiatric_array = [
		"Normal",
		"Depression",
		"Suicidal"
	];

	private $psychiatric_option_map1;
	private $psychiatric_option1_array;
	private $psychiatric_option2_array;

	//PHYSICAL EXAM: GENITALIA/RECTUM
	private $genitalia_rectum_array;

	function __construct() {

		$this->blood_option1_array = $this->normal_abnormal_array;
		$this->blood_option2_array = $this->normal_abnormal_array;

		$this->blood_option_map1 = [
			$this->blood_option1_array,
			$this->blood_option2_array
		];

		$this->feces_array = $this->normal_abnormal_array;
		$this->urine_array = $this->normal_abnormal_array;
		$this->pregnancy_array = $this->pregnant_options_array;

		$this->laboratory_exam_map1 = [
			$this->blood_array,
			$this->feces_array,
			$this->urine_array,
			$this->pregnancy_array
		];

		$this->laboratory_exam_map2 = [
			$this->blood_option_map1,
			NULL,
			NULL,
			NULL
		];

		$this->general_appearance_option1_array = $this->no_yes_array;
		$this->general_appearance_option2_array = $this->no_yes_array;
		$this->general_appearance_option3_array = $this->no_yes_array;

		$this->general_appearance_option_map1 = [
			$this->general_appearance_option1_array,
			$this->general_appearance_option2_array,
			$this->general_appearance_option3_array
		];

		$this->skin_option1_array = $this->skin_location_array;
		$this->skin_option1_option1_array = $this->normal_abnormal_array;
		$this->skin_option1_option2_array = $this->normal_abnormal_array;
		$this->skin_option1_option3_array = $this->normal_abnormal_array;
		$this->skin_option1_option4_array = $this->normal_abnormal_array;
		$this->skin_option1_option5_array = $this->normal_abnormal_array;
		$this->skin_option1_option6_array = $this->normal_abnormal_array;
		$this->skin_option1_option7_array = $this->normal_abnormal_array;
		$this->skin_option1_option8_array = $this->normal_abnormal_array;
		$this->skin_option1_option9_array = $this->normal_abnormal_array;
		$this->skin_option1_option10_array = $this->normal_abnormal_array;
		$this->skin_option1_option11_array = $this->normal_abnormal_array;

		$this->skin_option2_array = $this->skin_location_array;
		$this->skin_option2_option1_array = $this->skin_lesions_array;
		$this->skin_option2_option2_array = $this->skin_lesions_array;
		$this->skin_option2_option3_array = $this->skin_lesions_array;
		$this->skin_option2_option4_array = $this->skin_lesions_array;
		$this->skin_option2_option5_array = $this->skin_lesions_array;
		$this->skin_option2_option6_array = $this->skin_lesions_array;
		$this->skin_option2_option7_array = $this->skin_lesions_array;
		$this->skin_option2_option8_array = $this->skin_lesions_array;
		$this->skin_option2_option9_array = $this->skin_lesions_array;
		$this->skin_option2_option10_array = $this->skin_lesions_array;
		$this->skin_option2_option11_array = $this->skin_lesions_array;

		$this->skin_option_map1 = [
			$this->skin_option1_array,
			$this->skin_option2_array
		];

		$this->skin_option1_map1 = [
			$this->skin_option1_option1_array,
			$this->skin_option1_option2_array,
			$this->skin_option1_option3_array,
			$this->skin_option1_option4_array,
			$this->skin_option1_option5_array,
			$this->skin_option1_option6_array,
			$this->skin_option1_option7_array,
			$this->skin_option1_option8_array,
			$this->skin_option1_option9_array,
			$this->skin_option1_option10_array,
			$this->skin_option1_option11_array
		];

		$this->skin_option2_map1 = [
			$this->skin_option2_option1_array,
			$this->skin_option2_option2_array,
			$this->skin_option2_option3_array,
			$this->skin_option2_option4_array,
			$this->skin_option2_option5_array,
			$this->skin_option2_option6_array,
			$this->skin_option2_option7_array,
			$this->skin_option2_option8_array,
			$this->skin_option2_option9_array,
			$this->skin_option2_option10_array,
			$this->skin_option2_option11_array
		];

		$this->skin_option_map2 = [
			$this->skin_option1_map1,
			$this->skin_option2_map1,
			NULL
		];

		$this->head_option1_array = $this->no_yes_array;

		$this->head_option_map1 = [
			$this->head_option1_array,
			$this->head_option2_array
		];

		$this->eyes_option1_array = $this->normal_abnormal_array;

		$this->eyes_option7_array = $this->no_yes_array;

		$this->eyes_option1_array = $this->normal_abnormal_array;
		$this->eyes_option7_array = $this->no_yes_array;

		$this->eyes_option_map1 = [
			$this->eyes_option1_array,
			$this->eyes_option2_array,
			$this->eyes_option3_array,
			$this->eyes_option4_array,
			$this->eyes_option5_array,
			$this->eyes_option6_array,
			$this->eyes_option7_array
		];

		$this->ears_option4_array = $this->no_yes_array;

		$this->ears_option_map1 = [
			$this->ears_option1_array,
			$this->ears_option2_array,
			$this->ears_option3_array,
			$this->ears_option4_array
		];

		$this->nose_sinuses_option1_array = $this->no_yes_array;
		$this->nose_sinuses_option2_array = $this->no_yes_array;

		$this->nose_sinuses_option_map1 = [
			$this->nose_sinuses_option1_array,
			$this->nose_sinuses_option2_array
		];

		$this->mouth_pharynx_option_map1 = [
			$this->mouth_pharynx_option1_array,
			$this->mouth_pharynx_option2_array,
			$this->mouth_pharynx_option3_array
		];

		$this->neck_option2_array = $this->no_yes_array;

		$this->neck_option_map1 = [
			$this->neck_option1_array,
			$this->neck_option2_array,
			$this->neck_option3_array
		];

		$this->chest_breasts_option1_array = $this->no_yes_array;

		$this->chest_breasts_option_map1 = [
			$this->chest_breasts_option1_array,
			$this->chest_breasts_option2_array
		];

		$this->lungs_option1_array = $this->lungs_options_array;
		$this->lungs_option2_array = $this->lungs_options_array;
		$this->lungs_option_map1 = [
			$this->lungs_option1_array,
			$this->lungs_option2_array
		];

		$this->vascular_option1_array = $this->normal_abnormal_array;
		$this->vascular_option2_array = $this->normal_abnormal_array;
		$this->vascular_option3_array = $this->normal_abnormal_array;
		$this->vascular_option4_array = $this->normal_abnormal_array;

		$this->vascular_option_map1 = [
			$this->vascular_option1_array,
			$this->vascular_option2_array,
			$this->vascular_option3_array,
			$this->vascular_option4_array,
			$this->vascular_option5_array
		];

		$this->abdomen_option1_array = $this->no_yes_array;
		$this->abdomen_option2_array = $this->no_yes_array;
		$this->abdomen_option3_array = $this->no_yes_array;
		$this->abdomen_option4_array = $this->no_yes_array;
		$this->abdomen_option5_array = $this->abdomen_options_array;
		$this->abdomen_option6_array = $this->abdomen_options_array;
		$this->abdomen_option7_array = $this->no_yes_array;
		$this->abdomen_option8_array = $this->no_yes_array;

		$this->abdomen_option_map1 = [
			$this->abdomen_option1_array,
			$this->abdomen_option2_array,
			$this->abdomen_option3_array,
			$this->abdomen_option4_array,
			$this->abdomen_option5_array,
			$this->abdomen_option6_array,
			$this->abdomen_option7_array,
			$this->abdomen_option8_array
		];

		$this->musculoskeletal_option1_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option1_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option1_option2_array = $this->musculoskeletal_range_of_motion_array;


		$this->musculoskeletal_option2_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option2_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option2_option2_array = $this->musculoskeletal_range_of_motion_array;


		$this->musculoskeletal_option3_array = $this->musculoskeletal_upper_extremities_array;

		$this->musculoskeletal_option3_option1_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option3_option1_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option3_option1_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option3_option2_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option3_option2_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option3_option2_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option3_option3_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option3_option3_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option3_option3_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option3_option4_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option3_option4_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option3_option4_option2_array = $this->musculoskeletal_range_of_motion_array;


		$this->musculoskeletal_option4_array = $this->musculoskeletal_upper_extremities_array;

		$this->musculoskeletal_option4_option1_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option4_option1_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option4_option1_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option4_option2_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option4_option2_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option4_option2_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option4_option3_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option4_option3_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option4_option3_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option4_option4_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option4_option4_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option4_option4_option2_array = $this->musculoskeletal_range_of_motion_array;


		$this->musculoskeletal_option5_array = $this->musculoskeletal_lower_extremities_array;

		$this->musculoskeletal_option5_option1_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option5_option1_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option5_option1_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option5_option2_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option5_option2_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option5_option2_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option5_option3_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option5_option3_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option5_option3_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option5_option4_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option5_option4_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option5_option4_option2_array = $this->musculoskeletal_range_of_motion_array;


		$this->musculoskeletal_option6_array = $this->musculoskeletal_lower_extremities_array;

		$this->musculoskeletal_option6_option1_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option6_option1_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option6_option1_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option6_option2_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option6_option2_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option6_option2_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option6_option3_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option6_option3_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option6_option3_option2_array = $this->musculoskeletal_range_of_motion_array;

		$this->musculoskeletal_option6_option4_array = $this->musculoskeletal_options_array;

		$this->musculoskeletal_option6_option4_option1_array = $this->no_yes_array;

		$this->musculoskeletal_option6_option4_option2_array = $this->musculoskeletal_range_of_motion_array;


		$this->musculoskeletal_option_map1 = [
			$this->musculoskeletal_option1_array,
			$this->musculoskeletal_option2_array,
			$this->musculoskeletal_option3_array,
			$this->musculoskeletal_option4_array,
			$this->musculoskeletal_option5_array,
			$this->musculoskeletal_option6_array
		];


		$this->musculoskeletal_option1_map1 = [
			$this->musculoskeletal_option1_option1_array,
			$this->musculoskeletal_option1_option2_array
		];

		$this->musculoskeletal_option2_map1 = [
			$this->musculoskeletal_option2_option1_array,
			$this->musculoskeletal_option2_option2_array
		];

		$this->musculoskeletal_option3_map1 = [
			$this->musculoskeletal_option3_option1_array,
			$this->musculoskeletal_option3_option2_array,
			$this->musculoskeletal_option3_option3_array,
			$this->musculoskeletal_option3_option4_array
		];

		$this->musculoskeletal_option4_map1 = [
			$this->musculoskeletal_option4_option1_array,
			$this->musculoskeletal_option4_option2_array,
			$this->musculoskeletal_option4_option3_array,
			$this->musculoskeletal_option4_option4_array
		];

		$this->musculoskeletal_option5_map1 = [
			$this->musculoskeletal_option5_option1_array,
			$this->musculoskeletal_option5_option2_array,
			$this->musculoskeletal_option5_option3_array,
			$this->musculoskeletal_option5_option4_array
		];

		$this->musculoskeletal_option6_map1 = [
			$this->musculoskeletal_option6_option1_array,
			$this->musculoskeletal_option6_option2_array,
			$this->musculoskeletal_option6_option3_array,
			$this->musculoskeletal_option6_option4_array
		];

		$this->musculoskeletal_option_map2 = [
			$this->musculoskeletal_option1_map1,
			$this->musculoskeletal_option2_map1,
			$this->musculoskeletal_option3_map1,
			$this->musculoskeletal_option4_map1,
			$this->musculoskeletal_option5_map1,
			$this->musculoskeletal_option6_map1
		];


		$this->musculoskeletal_option3_option1_map1 = [
			$this->musculoskeletal_option3_option1_option1_array,
			$this->musculoskeletal_option3_option1_option2_array
		];

		$this->musculoskeletal_option3_option2_map1 = [
			$this->musculoskeletal_option3_option2_option1_array,
			$this->musculoskeletal_option3_option2_option2_array
		];

		$this->musculoskeletal_option3_option3_map1 = [
			$this->musculoskeletal_option3_option3_option1_array,
			$this->musculoskeletal_option3_option3_option2_array
		];

		$this->musculoskeletal_option3_option4_map1 = [
			$this->musculoskeletal_option3_option4_option1_array,
			$this->musculoskeletal_option3_option4_option2_array
		];

		$this->musculoskeletal_option4_option1_map1 = [
			$this->musculoskeletal_option4_option1_option1_array,
			$this->musculoskeletal_option4_option1_option2_array
		];

		$this->musculoskeletal_option4_option2_map1 = [
			$this->musculoskeletal_option4_option2_option1_array,
			$this->musculoskeletal_option4_option2_option2_array
		];

		$this->musculoskeletal_option4_option3_map1 = [
			$this->musculoskeletal_option4_option3_option1_array,
			$this->musculoskeletal_option4_option3_option2_array
		];

		$this->musculoskeletal_option4_option4_map1 = [
			$this->musculoskeletal_option4_option4_option1_array,
			$this->musculoskeletal_option4_option4_option2_array
		];

		$this->musculoskeletal_option5_option1_map1 = [
			$this->musculoskeletal_option5_option1_option1_array,
			$this->musculoskeletal_option5_option1_option2_array
		];

		$this->musculoskeletal_option5_option2_map1 = [
			$this->musculoskeletal_option5_option2_option1_array,
			$this->musculoskeletal_option5_option2_option2_array
		];

		$this->musculoskeletal_option5_option3_map1 = [
			$this->musculoskeletal_option5_option3_option1_array,
			$this->musculoskeletal_option5_option3_option2_array
		];

		$this->musculoskeletal_option5_option4_map1 = [
			$this->musculoskeletal_option5_option4_option1_array,
			$this->musculoskeletal_option5_option4_option2_array
		];

		$this->musculoskeletal_option6_option1_map1 = [
			$this->musculoskeletal_option3_option1_option1_array,
			$this->musculoskeletal_option3_option1_option2_array
		];

		$this->musculoskeletal_option6_option2_map1 = [
			$this->musculoskeletal_option6_option2_option1_array,
			$this->musculoskeletal_option6_option2_option2_array
		];

		$this->musculoskeletal_option6_option3_map1 = [
			$this->musculoskeletal_option6_option3_option1_array,
			$this->musculoskeletal_option6_option3_option2_array
		];

		$this->musculoskeletal_option6_option4_map1 = [
			$this->musculoskeletal_option6_option4_option1_array,
			$this->musculoskeletal_option6_option4_option2_array
		];

		$this->musculoskeletal_option3_map2 = [
			$this->musculoskeletal_option3_option1_map1,
			$this->musculoskeletal_option3_option2_map1,
			$this->musculoskeletal_option3_option3_map1,
			$this->musculoskeletal_option3_option4_map1
		];

		$this->musculoskeletal_option4_map2 = [
			$this->musculoskeletal_option4_option1_map1,
			$this->musculoskeletal_option4_option2_map1,
			$this->musculoskeletal_option4_option3_map1,
			$this->musculoskeletal_option4_option4_map1
		];

		$this->musculoskeletal_option5_map2 = [
			$this->musculoskeletal_option5_option1_map1,
			$this->musculoskeletal_option5_option2_map1,
			$this->musculoskeletal_option5_option3_map1,
			$this->musculoskeletal_option5_option4_map1
		];

		$this->musculoskeletal_option6_map2 = [
			$this->musculoskeletal_option6_option1_map1,
			$this->musculoskeletal_option6_option2_map1,
			$this->musculoskeletal_option6_option3_map1,
			$this->musculoskeletal_option6_option4_map1
		];

		$this->musculoskeletal_option_map3 = [
			NULL,
			NULL,
			$this->musculoskeletal_option3_map2,
			$this->musculoskeletal_option4_map2,
			$this->musculoskeletal_option5_map2,
			$this->musculoskeletal_option6_map2
		];


		$this->neurologic_option1_array = $this->normal_abnormal_array;
		$this->neurologic_option2_array = $this->neurologic_options_array;
		$this->neurologic_option3_array = $this->neurologic_options_array;
		$this->neurologic_option4_array = $this->normal_abnormal_array;

		$this->neurologic_option_map1 = [
			$this->neurologic_option1_array,
			$this->neurologic_option2_array,
			$this->neurologic_option3_array,
			$this->neurologic_option4_array
		];

		$this->neurologic_option2_option1_array = $this->normal_abnormal_array;
		$this->neurologic_option2_option2_array = $this->normal_abnormal_array;
		$this->neurologic_option2_option3_array = $this->normal_abnormal_array;
		$this->neurologic_option2_option4_array = $this->normal_abnormal_array;

		$this->neurologic_option2_map1 = [
			$this->neurologic_option2_option1_array,
			$this->neurologic_option2_option2_array,
			$this->neurologic_option2_option3_array,
			$this->neurologic_option2_option4_array
		];
		

		$this->neurologic_option3_option1_array = $this->normal_abnormal_array;
		$this->neurologic_option3_option2_array = $this->normal_abnormal_array;
		$this->neurologic_option3_option3_array = $this->normal_abnormal_array;
		$this->neurologic_option3_option4_array = $this->normal_abnormal_array;

		$this->neurologic_option3_map1 = [
			$this->neurologic_option3_option1_array,
			$this->neurologic_option3_option2_array,
			$this->neurologic_option3_option3_array,
			$this->neurologic_option3_option4_array
		];
		

		$this->neurologic_option_map2 = [
			NULL,
			$this->neurologic_option2_map1,
			$this->neurologic_option3_map1,
			NULL
		];

		$this->psychiatric_option1_array = $this->no_yes_array;
		$this->psychiatric_option2_array = $this->no_yes_array;

		$this->psychiatric_option_map1 = [
			$this->psychiatric_option1_array,
			$this->psychiatric_option2_array
		];
		
		$this->genitalia_rectum_array = $this->normal_abnormal_array;

		$this->physical_exam_map1 = [
			$this->general_appearance_array,
			$this->skin_array,
			$this->head_array,
			$this->eyes_array,
			$this->ears_array,
			$this->nose_sinuses_array,
			$this->mouth_pharynx_array,
			$this->neck_array,
			$this->chest_breasts_array,
			$this->lungs_array,
			$this->cardiac_array,
			$this->vascular_array,
			$this->abdomen_array,
			$this->musculoskeletal_array,
			$this->neurologic_array,
			$this->psychiatric_array,
			$this->genitalia_rectum_array
		];

		$this->physical_exam_map2 = [
			$this->general_appearance_option_map1,
			$this->skin_option_map1,
			$this->head_option_map1,
			$this->eyes_option_map1,
			$this->ears_option_map1,
			$this->nose_sinuses_option_map1,
			$this->mouth_pharynx_option_map1,
			$this->neck_option_map1,
			$this->chest_breasts_option_map1,
			$this->lungs_option_map1,
			NULL,
			$this->vascular_option_map1,
			$this->abdomen_option_map1,
			$this->musculoskeletal_option_map1,
			$this->neurologic_option_map1,
			$this->psychiatric_option_map1,
			NULL
		];

		$this->physical_exam_map3 = [
			NULL,
			$this->skin_option_map2,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			$this->musculoskeletal_option_map2,
			$this->neurologic_option_map2,
			NULL,
			NULL
		];

		$this->physical_exam_map4 = [
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			$this->musculoskeletal_option_map3,
			NULL,
			NULL,
			NULL
		];


		
	}

	public function getOptions($type, $category, $subcategory1, $subcategory2, $subcategory3, $subcategory4) {
		if($type === NULL) {
			return $this->exam_type_array;
		} else if ($category === NULL) {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				return $this->physical_exam_array;
			} else {
				return $this->laboratory_exam_array;
			}
		} else if ($subcategory1 === NULL) {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				if(isset($this->physical_exam_map1[$category])) {
					return $this->physical_exam_map1[$category];
				}
			} else {
				if(isset($this->laboratory_exam_map1[$category])) {
					return $this->laboratory_exam_map1[$category];
				}
			}
		} else if ($subcategory2 === NULL) {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				if(isset($this->physical_exam_map2[$category][$subcategory1-1])) {
					return $this->physical_exam_map2[$category][$subcategory1-1];
				}
			} else {
				if(isset($this->laboratory_exam_map2[$category][$subcategory1-1])) {
					return $this->laboratory_exam_map2[$category][$subcategory1-1];
				}
			}
		} else if ($subcategory3 === NULL) {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				if(isset($this->physical_exam_map3[$category][$subcategory1-1][$subcategory2-1])) {
					return $this->physical_exam_map3[$category][$subcategory1-1][$subcategory2-1];
				}
			} 
		} else {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				if(isset($this->physical_exam_map4[$category][$subcategory1-1][$subcategory2-1][$subcategory3-1])) {
					return $this->physical_exam_map4[$category][$subcategory1-1][$subcategory2-1][$subcategory3-1];
				}
			} 
		}
		return $this->normal_abnormal_array;
	} 

	public function isEnd($type, $category, $subcategory1, $subcategory2, $subcategory3, $subcategory4) {
		if($category === NULL) {
			return 1;
		} else if($subcategory1 === NULL) {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				return $this->physical_exam_map2[$category];
			} else {
				return $this->laboratory_exam_map2[$category];
			}
		} else if ($subcategory2 === NULL) {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				return $this->physical_exam_map3[$category][$subcategory1 - 1];
			} 
		} else if ($subcategory3 === NULL) {
			if ($type == $this->EXAM_TYPE_PHYSICAL) {
				return $this->physical_exam_map4[$category][$subcategory1 - 1][$subcategory2 - 1];
			} 
		} 
		return NULL;
	}

	public function getFullMapping() {
		$full_map = [];
		$level0_array = $this->getOptions(NULL, NULL, NULL, NULL, NULL, NULL);
		$level0_size = sizeof($level0_array);
		for($level0_index = 0; $level0_index < $level0_size; $level0_index++) {
			$level0_option = $level0_array[$level0_index];

			$level1_array = $this->getOptions($level0_index, NULL, NULL, NULL, NULL, NULL);
			if($this->isEnd($level0_index, NULL, NULL, NULL, NULL, NULL) == NULL) {
				$full_map[$level0_option] = $level1_array;
			} else {
				$level1_map = [];
				$level1_size = sizeof($level1_array);
				for($level1_index = 0; $level1_index < $level1_size; $level1_index++) {
					$level1_option = $level1_array[$level1_index];

					$level2_array = $this->getOptions($level0_index, $level1_index, NULL, NULL, NULL, NULL);
					if($this->isEnd($level0_index, $level1_index, NULL, NULL, NULL, NULL) == NULL) {
						$level1_map[$level1_option] = $level2_array;
					} else {
						$level2_map = [];
						$level2_size = sizeof($level2_array);
						for($level2_index = 1; $level2_index < $level2_size; $level2_index++) {
							$level2_option = $level2_array[$level2_index];

							$level3_array = $this->getOptions($level0_index, $level1_index, $level2_index, NULL, NULL, NULL);
							if($this->isEnd($level0_index, $level1_index, $level2_index, NULL, NULL, NULL) == NULL) {
								$level2_map[$level2_option] = $level3_array;
							} else {
								$level3_map = [];
								$level3_size = sizeof($level3_array);
								for($level3_index = 1; $level3_index < $level3_size; $level3_index++) {
									$level3_option = $level3_array[$level3_index];

									$level4_array = $this->getOptions($level0_index, $level1_index, $level2_index, $level3_index, NULL, NULL);
									if($this->isEnd($level0_index, $level1_index, $level2_index, $level3_index, NULL, NULL) == NULL) {
										$level3_map[$level3_option] = $level4_array;
									} else {
										$level4_map = [];
										$level4_size = sizeof($level4_array);
										for($level4_index = 1; $level4_index < $level4_size; $level4_index++) {
											$level4_option = $level4_array[$level4_index];

											$level5_array = $this->getOptions($level0_index, $level1_index, $level2_index, $level3_index, $level4_index, NULL);
											if($this->isEnd($level0_index, $level1_index, $level2_index, $level2_index, $level3_index, $level4_index, NULL) == NULL) {
												$level4_map[$level4_option] = $level5_array;
											} else {
												$level5_map = array();
												$level5_size = sizeof($level5_array);
												for($level5_index = 1; $level5_index < $level5_size; $level5_index++) {
													$level5_option = $level5_array[$level5_index];

													$level6_array = $this->getOptions($level0_index, $level1_index, $level2_index, $level3_index, $level4_index, $level5_index);
													$level5_map[$level5_option] = $level6_array;
												}
												$level4_map[$level4_option] = $level5_map;
											}
										}
										$level3_map[$level3_option] = $level4_map;
									}
								}
								$level2_map[$level2_option] = $level3_map;
							}
						}
						$level1_map[$level1_option] = $level2_map;
					}
				}
				$full_map[$level0_option] = $level1_map;
			}

		}
		return $full_map;

	}



}

?>