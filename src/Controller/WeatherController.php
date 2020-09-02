<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Service\WeatherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WeatherController extends AbstractController
{
    private $weatherService;

    public function __construct(WeatherService $weather)
    {
        $this->weatherService = $weather;
    }

    /**
     * @Route("/", name="accueil")
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

            return $this->redirectToRoute('meteo_ville',[
                'ville' => $ville->getNom()
            ]);
        }

        $ligne = $this->weatherService->getDefaultVille();

        if (is_array($ligne)) {
            $datas = [
                'lon' => $ligne['coord']['lon'],
                'lat' => $ligne['coord']['lat'],
                'wid' => $ligne['weather'][0]['id'],
                'condition' => $ligne['weather'][0]['main'],
                'description' => ucfirst($ligne['weather'][0]['description']),
                'icon_css' => $this->icon_css($ligne['weather'][0]['id']),
                'icon_img' => $this->icon_img($ligne['weather'][0]['icon']),
                'base' => $ligne['base'],
                'temperature' => round($ligne['main']['temp']),
                'pressure' => $ligne['main']['pressure'],
                'humidity' => $ligne['main']['humidity'] . "%",
                'min' => round($ligne['main']['temp_min']),
                'max' => round($ligne['main']['temp_max']),
                'wind_speed' => $this->days(0, $ligne['wind']['speed']),
                'wind_deg' => $ligne['wind']['deg'],
                'country_code' => $ligne['sys']['country'],
                'sunrise' => $ligne['sys']['sunrise'],
                'sunset' => $ligne['sys']['sunset'],
                'country_id' => $ligne['id'],
                'country_name' => $ligne['name'],
                'code' => $ligne['cod'],
                'date' => date("d/m/Y", $ligne['dt']),
                'day' => $this->days(1, gmdate("w", $ligne['dt'])),
            ];
        }
        return $this->render('weather/accueil.html.twig', [
            'form' => $form->createView(),
            'data' => $datas
            ]);
    }


    /**
     * @Route("/weather/{ville}", name="meteo_ville")
     */
    public function getWeatherData($ville)
    {
        $ligne = $this->weatherService->getWeather($ville);

        if (is_array($ligne)) {

            $datas = [
                'lon' => $ligne['coord']['lon'],
                'lat' => $ligne['coord']['lat'],
                'wid' => $ligne['weather'][0]['id'],
                'condition' => $ligne['weather'][0]['main'],
                'description' => ucfirst($ligne['weather'][0]['description']),
                'icon_css' => $this->icon_css($ligne['weather'][0]['id']),
                'icon_img' => $this->icon_img($ligne['weather'][0]['icon']),
                'base' => $ligne['base'],
                'temperature' => round($ligne['main']['temp']),
                'pressure' => $ligne['main']['pressure'],
                'humidity' => $ligne['main']['humidity'] . "%",
                'min' => round($ligne['main']['temp_min']),
                'max' => round($ligne['main']['temp_max']),
                'wind_speed' => $this->days(0, $ligne['wind']['speed']),
                'wind_deg' => $ligne['wind']['deg'],
                'country_code' => $ligne['sys']['country'],
                'sunrise' => $ligne['sys']['sunrise'],
                'sunset' => $ligne['sys']['sunset'],
                'country_id' => $ligne['id'],
                'country_name' => $ligne['name'],
                'code' => $ligne['cod'],
                'date' => date("d/m/Y", $ligne['dt']),
                'day' => $this->days(1, gmdate("w", $ligne['dt'])),
            

            ];
            return $this->render('weather/resultat.html.twig', [
                'data' => $datas,
                'ville' => $ligne['name']
            ]);

        } else {

            return $this->render('error.html.twig', [
                'error' => $ligne
                ]);
        }
      }


    public function icon_img($icon = null)
    {
        return 'http://openweathermap.org/img/w/' . $icon . '.png';
    }

    public function icon_css($code = null)
    {
        // https://erikflowers.github.io/weather-icons/api-list.html
        return "wi wi-owm-" . $code;
    }

    public function days($type, $datas)
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
            return $days[$datas];
        } else {
            return round($datas * 3600 / 1000, 2) . ' km/h';
        }
    }
}
