<?php
##
## clsFractions.php
##
## Class for fraction manipulation
## 
## Code: s k r a t c h
##

class Fractions {
	## CLASS Fractions
	##  Class to perform vaious mathematical manipulation with fractions.
	##  Fractions are submitted in the following array manner:
	##    farray[i][0] = whole number
	##    farray[i][1] = numerator
	##    farray[i][2] = denominator
	
	## Variable declaration
	var $farray_original; # Original array submitted
	var $farray_current;  # Current state of array after manipulation
	
	## Constructor
	
	function Fractions($fraction_array) {
		## FUNCTION Fractions
		##  Constructor for class Fractions.
		##  Takes in a set of fractions in array form as input.
		##  The constructor will only store the array to variable.
		## Input: fraction_array
		##    fraction_array[i][0] = whole number
		##    fraction_array[i][1] = numerator
		##    fraction_array[i][2] = denominator
		## Output: handle to instance of class
		
		$this->farray_original = $fraction_array;
		$this->farray_current  = $fraction_array;
		return (bool) TRUE;
	}
	
	function fmb($intgr,$numer,$denom,$factor) {
		## FUNCTION fmb() (Fraction Multiply By)
		## 	Take a mixed fraction (example 1 2/3 ) and multiply by a specified factor.
		## 	Return an array with whole, numerator, denominator
		## Input: intgr, the whole portion of the fraction
		##        numer, the numeator of the fraction
		##        denom, the denominator of the fraction
		## Output: array containing new_intgr, new_numer, new_denom

		$new_intgr  = $intgr * $factor;
		$numer_temp = $numer * $factor;
		$new_denom  = $denom;
		if ($denom != 0):
			$new_numer = $numer_temp % $denom;
			$new_intgr += floor($numer_temp / $denom);
		else:
			$new_numer = 0;
			$new_denom = 0;
		endif;
		if ($new_numer == 0):
			$new_denom = 0;
		else:
			$arr = $this->reduce($new_numer,$new_denom);
			$new_numer = $arr[0];
			$new_denom = $arr[1];
		endif;
		return array($new_intgr,$new_numer,$new_denom);
	}
	
	function mult_by($factor) {
		## FUNCTION mult_by
		##  Multiply all fractions in set by a factor given.
		## Input: $factor, number to multiply by
		## Output: new fractions array, and all fractions in set are updated
		
		$num_fractions = count($this->farray_current);
		for ($a = 0; $a < $num_fractions; $a++) {
			# take slice containing only integer, numerator,denominator
			$mixed = array_slice($this->farray_current[$a],0,3);
			$new_mixed = $this->fmb($mixed[0],$mixed[1],$mixed[2],$factor);
			$this->farray_current[$a][0] = $new_mixed[0];
			$this->farray_current[$a][1] = $new_mixed[1];
			$this->farray_current[$a][2] = $new_mixed[2];
		}
		return $this->farray_current;
	}
	
	function gcd($a,$b) {
		## FUNCTION gcd
		##  Euclid's Algorithm (to find the gcd - Greatest Common Divisor)
		## Input: $a,$b
		## Output: GCD of $a and $b
		
		while ( $b > 0 )
		{
			$remainder = $a % $b;
			$a = $b;
			$b = $remainder;
		}
		return abs( $a );
	}
	
	function reduce($numerator,$denominator) {
		## FUNCTION reduce
		##  Reduce the fraction
		## Input: $numerator, $denominator
		## Output: updated numerator and denominator
		
		$gcd = $this->gcd($numerator,$denominator);
		$numerator /= $gcd;
		$denominator /= $gcd;
		return array($numerator,$denominator);
	}
	
	function find_ratio($fracs) {
		## FUNCTION find_ratio 
		##  For an array of $fractions, generate an equivalent ratio.
		##  This is done by finding a common denominator for all fractions
		##  ('LCD' in the code) and the ratio is the set of numerators.
		## Input: Fractions in format :
		##         fracs[i][0] = whole
		##         fracs[i][1] = numerator
		##         fracs[i][2] = denominator
		## Output: array containing ratio, and LCD is stored in key "LCD"

		$num_fractions = count($fracs);
		
		# Build arrray with only numerator, denominator
		for ($a = 0; $a < $num_fractions; $a++) {
			# Start with numerator and denominator
			$fractions[$a][0] = $fracs[$a][1];
			$fractions[$a][1] = $fracs[$a][2];
			if ($fracs[$a][0] != 0 && $fracs[$a][2] != 0):
				# Mixed fraction, turn into improper fraction
				$add = $fracs[$a][0] * $fracs[$a][2];
				$fractions[$a][0] += $add;
			elseif ($fracs[$a][0] != 0 && $fracs[$a][2] == 0):
				# Whole number, make denominator 1 and numerator the whole number
				$fractions[$a][0] = $fracs[$a][0];
				$fractions[$a][1] = 1;
			endif;
		}
		
		$common["LCD"] = "";

		for ($a = 0; $a < $num_fractions; $a++) {
			if ($common["LCD"] == "") {
				# If first fraction, add numerator to list and set LCD
				$common["LCD"] = $fractions[$a][1];
				$common[0] = $fractions[$a][0];
				continue;
			}	

			# write to variables (n1/d1, n2/d2)
			$d1 = $common["LCD"];
			$d2 = $fractions[$a][1];
			$n2 = $fractions[$a][0];

			# Calculate GCD using function gcd()
			$GCF = $this->gcd($d1,$d2);

			# Calculate new LCD
			$common["LCD"] = $d1 * $d2 / $GCF;

			## Update new numerators
			foreach (array_keys($common) as $key) {
				if ((string)$key == "LCD") continue;
				$common[$key] = $common[$key] * $d2 / $GCF;
			}
			## Add the latest updated numerator to the new array
			$common[$a] = $d1 * $n2 / $GCF;
		}
		return $common;
	}
}
?>