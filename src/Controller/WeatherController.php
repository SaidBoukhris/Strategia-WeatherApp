<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Service\WeatherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WeatherController extends AbstractController
{
    private $weatherService;

    public function __construct(WeatherService $weather)
    {
        $this->weatherService = $weather;
    }

    /**
     * @Route("/", name="main")
     */
    public function index(Request $request)
    {
        $ville = new Ville();

        $form = $this->createFormBuilder($ville)
            ->add('nom', TextType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ville = $form->getData();

            return $this->redirectToRoute('weather_ville',['ville' => $ville->getNom()]);
        }
        return $this->render('weather/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/weather/{ville}", name="weather_ville")
     */
    public function getWeatherData($ville)
    {
      // data generation
        // source: https://github.com/wadday/openweather/blob/master/src/Wadday/Openweather/Wadday.php
        $WeatherDataRaw = $this->weatherService->getWeather($ville);
        // dd($WeatherDataRaw);
        // if no error
        if (is_array($WeatherDataRaw)) {
            $data = [
                //lontitude
                'lon' => $WeatherDataRaw['coord']['lon'],
                //latitude
                'lat' => $WeatherDataRaw['coord']['lat'],
                //weather
                'wid' => $WeatherDataRaw['weather'][0]['id'],
                'condition' => $WeatherDataRaw['weather'][0]['main'],
                'description' => ucfirst($WeatherDataRaw['weather'][0]['description']),
                //weather
                'icon_css' => $this->icon_css($WeatherDataRaw['weather'][0]['id']),
                'icon_img' => $this->icon_img($WeatherDataRaw['weather'][0]['icon']),
                'base' => $WeatherDataRaw['base'],
                //main
                'temperature' => round($WeatherDataRaw['main']['temp']),
                'pressure' => $WeatherDataRaw['main']['pressure'],
                'humidity' => $WeatherDataRaw['main']['humidity'] . "%",
                'min' => round($WeatherDataRaw['main']['temp_min']),
                'max' => round($WeatherDataRaw['main']['temp_max']),

                //wind
                'wind_speed' => $this->transformDays(0, $WeatherDataRaw['wind']['speed']),
                'wind_deg' => $WeatherDataRaw['wind']['deg'],
                //sys
                'country_code' => $WeatherDataRaw['sys']['country'],
                'sunrise' => $WeatherDataRaw['sys']['sunrise'],
                'sunset' => $WeatherDataRaw['sys']['sunset'],
                //general
                'country_id' => $WeatherDataRaw['id'],
                'country_name' => $WeatherDataRaw['name'],
                'code' => $WeatherDataRaw['cod'],
                'date' => date("d/m/Y", $WeatherDataRaw['dt']),
                'day' => $this->transformDays(1, gmdate("w", $WeatherDataRaw['dt'])),
            

            ];
                // dd($WeatherMonths);
                return $this->render('weather/result.html.twig', array("data" => $data,'ville' => $WeatherDataRaw['name']));
        } else {
            // 
            return $this->render('errors.html.twig', array("error" => $WeatherDataRaw));
        }
      }


    public function icon_img($icon = null)
    {
        return 'http://openweathermap.org/img/w/' . $icon . '.png';
    }

    public function icon_css($code = null)
    {
        return "wi wi-owm-" . $code;
    }

    public function transformDays($type, $data)
    {

        if ($type == 1) {
            $days = array(
                'Dimanche',
                'Lundi',
                'Mardi',
                'Mercredi',
                'Jeudi',
                'Vendredi',
                'Samedi'
            );
            return $days[$data];
        } else {
            // Transform m/s to km/s
            return round($data * 3600 / 1000, 2) . ' km/h';
        }
    }
}
