<?php

class Ticker
{
	public function __construct()
	{
		$view = new View( "index" );
		
		$items = array(
			"callback_ticketlist" => array( "ticketentry" , $this , "callback_ticketlist" ),
			"title" => "Lab ticker",
			"refreshTime" => __REFRESH_TIME
		);
		
		$view->process( $items );
		$view->output();
	}

	private function get_dept_exclusions( )
	{
		$depart_excl = array();

		if(strpos(__DEPARTMENT_EXCLUSIONS, "|") > 0 )
		{
			$depts = explode("|", __DEPARTMENT_EXCLUSIONS);

			foreach ($depts as $dept) 
			{
				array_push($depart_excl, array("!=", $dept));
			}
		}
		else
		{
			$depts = __DEPARTMENT_EXCLUSIONS;
			array_push($depart_excl, array("!=", $depts));
		}

		return $depart_excl;
	}

	private static function sortBy($a, $b)
	{
		$a_status = $a->getStatus()->getTitle();
	    $b_status = $b->getStatus()->getTitle();

		if(__SORT_METHOD == "ID")
	    {
	    	$one = $a->getId();
	    	$two = $b->getId();
	    }
	    elseif (__SORT_METHOD == "CREATION_DATE") 
	    {
	    	$one = $a->getCreationTime();
	    	$two = $b->getCreationTime();
	    }

		if ( $one == $two ) 
		{
			return 0;
		}
		
		if ( __SORT_DIRECTION == "DESC")
		{
			return ($one < $two ) ? 1 : -1;
		}

		return ($one < $two ) ? -1 : 1;
	}

	private function filter_by_depts( )
	{
		$departments = kyDepartment::getAll();

		foreach ($this->get_dept_exclusions() as $value) 
		{
			$departments = $departments->filterByTitle( $value );
		}

		return $departments;
	}

	public function callback_ticketlist( $match )
	{		
		$itemlist = "";
		
		$tickets = array();
		$i=1;

		$ticketStatus = (strpos(__TICKET_STATUS, "|") > 0) ? explode("|", __TICKET_STATUS) : __TICKET_STATUS;
		$ticketType = (strpos(__TICKET_TYPE, "|") > 0) ? explode("|", __TICKET_TYPE) : __TICKET_TYPE;
				
		if( is_array( $ticketStatus ) )
		{
			$departments = $this->filter_by_depts();

			foreach ($ticketStatus as $status) 
			{
				if( is_array( $ticketType ) )
				{
					foreach ($ticketType as $type) 
					{
						$ticketsearch = kyTicket::getAll
						( 
							$departments, 
							kyTicketStatus::getAll()->filterByTitle( $status )
						)->filterByTypeId( kyTicketType::getAll()->filterByTitle( $type )->first()->getId() );
					
						if(count($ticketsearch->getRawArray()) > 0)
						{
							$ticketsfound = $ticketsearch->getRawArray();
							usort($ticketsfound, array("Ticker", "sortBy"));
							$tickets = array_merge($tickets, $ticketsfound);
						}
					}
				}
				else
				{

					$ticketsearch = kyTicket::getAll
					( 
						$departments, 
						kyTicketStatus::getAll()->filterByTitle( $status )
					)->filterByTypeId( kyTicketType::getAll()->filterByTitle( $ticketType )->first()->getId() );

					if(count($ticketsearch->getRawArray()) > 0)
					{
						$ticketsfound = $ticketsearch->getRawArray();
						usort($ticketsfound, array("Ticker", "sortBy"));
						$tickets = array_merge($tickets, $ticketsfound);
					}
				}
			}
		}
		else
		{
			$departments = $this->filter_by_depts();

			if( is_array( $ticketType ) )
			{
				foreach ($ticketType as $type) 
				{
					$ticketsearch = kyTicket::getAll
					( 
						$departments,  
						kyTicketStatus::getAll()->filterByTitle( $ticketStatus )
					)->filterByTypeId( kyTicketType::getAll()->filterByTitle( $type )->first()->getId() );

					if(count($ticketsearch->getRawArray()) > 0)
					{
						$ticketsfound = $ticketsearch->getRawArray();
						usort($ticketsfound, array("Ticker", "sortBy"));
						$tickets = array_merge($tickets, $ticketsfound);
					}
				}
			}
			else
			{
				$ticketsearch = kyTicket::getAll
				( 
					$departments,  
					kyTicketStatus::getAll()->filterByTitle( $ticketStatus )
				)->filterByTypeId( kyTicketType::getAll()->filterByTitle( $ticketType )->first()->getId() );

				if(count($ticketsearch->getRawArray()) > 0)
				{
					$ticketsfound = $ticketsearch->getRawArray();
					usort($ticketsfound, array("Ticker", "sortBy"));
					$tickets = array_merge($tickets, $ticketsfound);
				}
			}
		}

		foreach ($tickets as $kyTicket)
		{

			if( ( $i <= __MAX_TICKETS ) || __MAX_TICKETS == -1 )
			{			
				$item = $match;

				$subject = $kyTicket->getSubject();
				if( strlen($subject) >= 59 )
				{
					$subject = substr($subject, 0, 56);
					$subject .= "...";
				}

				$status = $kyTicket->getStatus()->getTitle();
				if($status == "Open")
				{
					$class = "open";
				}
				elseif ($status == "In Progress") 
				{
					$class = "inProgress";
				}
				elseif ($status == "Closed") 
				{
					$class = "closed";
				}
				elseif ($status == "On Hold") 
				{
					$class = "onHold";
				}

				$item = preg_replace( "/\\[class\\]/s" , $class , $item );
				$item = preg_replace( "/\\[ticketid\\]/s" , $kyTicket->getId() , $item );
				$item = preg_replace( "/\\[user\\]/s" , $kyTicket->getFullName() , $item );
				$item = preg_replace( "/\\[subject\\]/s" , $subject , $item );
				$item = preg_replace( "/\\[status\\]/s" , $status , $item );
				
				
				$item = preg_replace( "/\\[dept\\]/s" , $kyTicket->getDepartment()->getTitle() , $item );
				$item = preg_replace( "/\\[creation\\]/s" , $kyTicket->getCreationTime() , $item );
				$item = preg_replace( "/\\[type\\]/s" , $kyTicket->getType()->getTitle() , $item );
				
				$itemlist .= $item;

				$i++;
			}
		}
		return $itemlist;
	}
}