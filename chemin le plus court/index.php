<?php

function parseLaby(array $laby, string $mur = "#", $espace = ' ', string $debut = "@", string $fin = "X"){

    $points = [];
    $x = 0;
    foreach ($laby as $ligne)
    {
        for ($y = 0; $y < strlen($ligne); $y++)
        {
            $coords = ['x' => $x, 'y' => $y];
            switch ($ligne[$y]) {
                case $mur:
                    $points[] = ['coords' => $coords, 'type' => 'mur'];
                    break;
                case $espace:
                    $points[] = ['coords' => $coords, 'type' => 'espace'];
                    break;
                case $debut:
                    $startPoint = ['coords' => $coords, 'type' => 'debut'];
                    $points[] = $startPoint;
                    break;
                case $fin:
                    $points[] = ['coords' => $coords, 'type' => 'fin'];
                    break;
            }
        }
       $x++;
    }
    return [
        'points' => $points,
        'startPoint' => $startPoint,
        ];
}

function getPositionType($points, $coords)
{
    foreach ($points as $point)
    {
        if ($point['coords'] == $coords)
        {
            return $point['type'];
        }
    }
    return false;
}

function getNewDecision($points, $chemin, $positionActuel)
{
    $decision = [];
    $haut = ['x' => $positionActuel['coords']['x'] + 1, 'y' => $positionActuel['coords']['y']];
    $bas = ['x' => $positionActuel['coords']['x'] - 1, 'y' => $positionActuel['coords']['y']];
    $droite = ['x' => $positionActuel['coords']['x'], 'y' => $positionActuel['coords']['y'] + 1];
    $gauche = ['x' => $positionActuel['coords']['x'], 'y' => $positionActuel['coords']['y'] - 1];

    $decision['centre'] = $positionActuel['coords'];
    $decision['chemin'] = $chemin;
    
    $lastPosition = $positionActuel['coords'];
    if (!empty($chemin))
    {
        $lastPosition = $chemin[count($chemin) - 1];
    }   
    
    if (($type = getPositionType($points, $droite)) != 'mur' && $lastPosition != $droite)
    {
        $choix = ['coords' => $droite, 'etat' => false];
        if ($type == 'fin')
        {
            $decision['choix'] = []; 
        }
        $decision['choix'][] = $choix;
    }

    if (($type = getPositionType($points, $gauche)) != 'mur' && $lastPosition != $gauche)
    {
        $choix = ['coords' => $gauche, 'etat' => false];
        if ($type == 'fin')
        {
            $decision['choix'] = []; 
        }
        $decision['choix'][] = $choix;
    }

    if (($type = getPositionType($points, $haut)) != 'mur' && $lastPosition != $haut)
    {
        $choix = ['coords' => $haut, 'etat' => false];
        if ($type == 'fin')
        {
            $decision['choix'] = []; 
        }
        $decision['choix'][] = $choix;
    }

    if (($type = getPositionType($points, $bas)) != 'mur' && $lastPosition != $bas)
    {
        $choix = ['coords' => $bas, 'etat' => false];
        if ($type == 'fin')
        {
            $decision['choix'] = []; 
        }
        $decision['choix'][] = $choix;
    
    }
    return $decision;
}

function retournAncienneDecisionIndex($listDecision)
{
    $numberDecisions = count($listDecision);
    
    $nomberDecisions = 0;
        if (!empty($listDecision))
        {
            $nomberDecisions= count($listDecision);
        }

    for ($i = $numberDecisions - 1; $i >= 0; $i--)
    {
        

        foreach ($listDecision[$i]['choix'] as $choix)
            {
                if ($choix['etat'] == false)
                {
                    return $i;
                } 
            }
    }
    return -1;
}

function getDecisionIndexWithCoords($listDecision, $coords)
{
    $index = 0;
    foreach ($listDecision as $decision)
    {
        if ($decision['centre']['x'] == $coords['x'] &&
        $decision['centre']['y'] == $coords['y'])
        {
            return $index;
        }
        $index++;
    }
    return -1;
}

function plusCourtChemin(string $path = './laby.txt', string $mur = "#", $espace = ' ', string $debut = "@", string $fin = "X"){
    $lignes = file($path);
    $laby = parseLaby($lignes, $mur, $espace, $debut, $fin);
    $points = $laby['points'];

    $positionActuel = $laby['startPoint'];
    $chemin = [];
    $listDecision = [];


    while ($positionActuel['type'] != 'fin')
    {
        
        
        $index = getDecisionIndexWithCoords($listDecision, $positionActuel['coords']);
        

        if ($index == -1)
        {
            $decision = getNewDecision($points, $chemin, $positionActuel);
        }
        else
        {
            $decision = &$listDecision[$index];
        }
        $nombreChoix = 0;
        if (!empty($decision['choix']))
        {
            $nombreChoix = count($decision['choix']);
        }
      
        if ($nombreChoix > 1)
        {
            $chemin[] = $positionActuel['coords'];
            
            foreach ($decision['choix'] as &$choix)
            {
                if ($choix['etat'] == false)
                {
                    $choix['etat'] = true;
                    $positionActuel['coords'] = $choix['coords'];
                    $positionActuel['type'] = getPositionType($points, $choix['coords']);
                    break;
                }
            }
            
            if ($index == -1)
            {
                $listDecision[] = $decision;
            }  
                 
        }
        else if ($nombreChoix == 1)
        {
            $chemin[] = $positionActuel['coords'];
            $positionActuel['coords'] = $decision['choix'][0]['coords'];
            $positionActuel['type'] = getPositionType($points, $decision['choix'][0]['coords']);
        }
        else
        {
            
            $decisionIndex = retournAncienneDecisionIndex($listDecision);
            
            if ($decisionIndex == -1)
                return "ERROR THE MAZE IN NOT SOLVABLE!!";
            $positionActuel['coords'] = $listDecision[$decisionIndex]['centre'];
            $positionActuel['type'] = getPositionType($points, $listDecision[$decisionIndex]['centre']);
            $chemin = $listDecision[$decisionIndex]['chemin'];
        }
    }
    
    $lignes = ecrireLeChemin($chemin, $lignes);
    foreach ($lignes as $ligne){
        
    }
    
    return convertToText($lignes);
}

function ecrireLeChemin(array $chemin, array $lignes){
    foreach ($chemin as $value) {
        $lignes[$value['x']][$value['y']] = '.';
    }

    return $lignes;
}

function convertToText(array $array){
    $result = '';
    foreach ($array as $value) {
        for ($i = 0 ; $i < strlen($value); $i++) {
            $result .= $value[$i];
        }
        $result .= "\n";
    }

    return $result;
}

var_dump(plusCourtChemin());